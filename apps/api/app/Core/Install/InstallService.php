<?php

declare(strict_types=1);

namespace App\Core\Install;

use App\Core\Requirements\EnvironmentRequirementChecker;
use App\Core\Requirements\RequirementReport;
use App\Models\User;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Pages\Services\StarterSiteService;
use App\Modules\Setup\Services\InstallationStateService;
use App\Modules\Setup\Services\SetupLogService;
use App\Modules\Settings\Services\SettingsService;
use App\Modules\Users\Database\Seeders\RolesAndPermissionsSeeder;
use App\Modules\Users\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class InstallService
{
    public function __construct(
        private readonly EnvironmentRequirementChecker $requirements,
        private readonly InstallationStateService $installationState,
        private readonly SetupLogService $setupLog,
        private readonly StarterSiteService $starterSite,
        private readonly OnboardingService $onboarding,
        private readonly SettingsService $settings,
    ) {}

    public function requirements(): RequirementReport
    {
        return $this->requirements->check();
    }

    /**
     * @param  array{driver?: string, host?: string, port?: int|string, database?: string, username?: string, password?: string|null}  $database
     */
    public function testDatabaseConnection(array $database): bool
    {
        $driver = $database['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $path = $database['database'] ?? database_path('database.sqlite');
            File::ensureDirectoryExists(dirname($path));
            if (! file_exists($path)) {
                touch($path);
            }

            return is_writable($path);
        }

        $dsn = match ($driver) {
            'mysql', 'mariadb' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s',
                $database['host'] ?? '127.0.0.1',
                $database['port'] ?? 3306,
                $database['database'] ?? '',
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $database['host'] ?? '127.0.0.1',
                $database['port'] ?? 5432,
                $database['database'] ?? '',
            ),
            default => throw new RuntimeException("Unsupported database driver: {$driver}"),
        };

        new \PDO(
            $dsn,
            (string) ($database['username'] ?? ''),
            (string) ($database['password'] ?? ''),
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        );

        return true;
    }

    public function install(InstallOptions $options): void
    {
        if ($this->installationState->isInstalled()) {
            throw new RuntimeException('Luma CMS is already installed.');
        }

        $report = $this->requirements();
        if (! $report->passed()) {
            throw new RuntimeException('Environment requirements are not satisfied.');
        }

        $this->log('requirements', 'success', 'Environment requirements passed.');

        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
            $this->log('app_key', 'success', 'Application key generated.');
        }

        Artisan::call('migrate', ['--force' => true]);
        $this->log('migrate', 'success', trim(Artisan::output()) ?: 'Migrations completed.');

        Artisan::call('db:seed', [
            '--class' => RolesAndPermissionsSeeder::class,
            '--force' => true,
        ]);
        $this->log('seed', 'success', 'Roles and permissions seeded.');

        $admin = $this->ensureAdminUser($options);
        $this->log('admin', 'success', "Admin user ready: {$admin->email}");

        if ($options->siteTitle) {
            $this->settings->set('site.title', $options->siteTitle);
        }

        if ($options->withStarterSite) {
            $this->starterSite->installPreset('business', $admin);
            $this->log('starter_site', 'success', 'Starter site content installed.');
            $this->onboarding->markFullyCompletedFromInstall();
        }

        if (! File::exists(public_path('storage'))) {
            Artisan::call('storage:link');
            $this->log('storage_link', 'success', 'Public storage linked.');
        }

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $this->log('cache', 'success', 'Caches cleared.');

        $this->installationState->markInstalled((string) config('luma.version', '0.0.0-dev'));
        $this->log('finish', 'success', 'Installation completed.');
    }

    private function ensureAdminUser(InstallOptions $options): User
    {
        $email = $options->adminEmail ?: env('LUMA_SEED_ADMIN_EMAIL', 'admin@luma.test');
        $password = $options->adminPassword ?: env('LUMA_SEED_ADMIN_PASSWORD', 'password');

        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $options->siteTitle ?: ($user->name ?: 'Luma Admin');
        $user->password = Hash::make($password);
        $user->save();
        $user->roles()->sync([$adminRole->id]);

        return $user;
    }

    private function log(string $step, string $status, string $message): void
    {
        $this->setupLog->write($step, $status, $message);
    }
}
