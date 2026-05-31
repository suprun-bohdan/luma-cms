<?php

declare(strict_types=1);

namespace Tests\Feature\Setup;

use App\Modules\Setup\Models\LumaInstallation;
use App\Modules\Setup\Services\InstallationStateService;
use App\Modules\Users\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SharedHostingInstallScript;
use Tests\TestCase;

final class SetupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_status_is_not_installed_initially(): void
    {
        $this->getJson('/api/v1/setup/status')
            ->assertOk()
            ->assertJsonPath('installed', false)
            ->assertJsonPath('version', '0.0.25-rc.11');
    }

    public function test_sqlite_database_test_uses_default_path_when_empty(): void
    {
        config(['luma.setup_token' => '']);

        $path = database_path('database.sqlite');
        if (is_file($path)) {
            unlink($path);
        }

        $this->postJson('/api/v1/setup/database/test', [
            'driver' => 'sqlite',
        ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertFileExists($path);
    }

    public function test_save_database_writes_sqlite_settings_to_env(): void
    {
        config(['luma.setup_token' => '']);

        $envPath = base_path('.env');
        $backup = is_file($envPath) ? file_get_contents($envPath) : null;

        try {
            file_put_contents($envPath, "APP_NAME=Luma\nDB_CONNECTION=mysql\n");

            $this->postJson('/api/v1/setup/database', [
                'driver' => 'sqlite',
                'database' => database_path('database.sqlite'),
            ])
                ->assertOk()
                ->assertJsonPath('ok', true);

            $contents = (string) file_get_contents($envPath);
            $this->assertStringContainsString('DB_CONNECTION=sqlite', $contents);
            $this->assertStringContainsString('database.sqlite', $contents);
        } finally {
            if ($backup === null) {
                @unlink($envPath);
            } else {
                file_put_contents($envPath, $backup);
            }

            @unlink(storage_path('framework/env-writer.bak'));
            @unlink(storage_path('framework/env-writer.lock'));
        }
    }

    public function test_setup_status_and_logs_work_when_database_is_unreachable(): void
    {
        $state = app(InstallationStateService::class);
        $marker = $state->installedMarkerPath();
        $hadMarker = is_file($marker);
        if ($hadMarker) {
            unlink($marker);
        }

        $originalMysql = config('database.connections.mysql');

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => 1,
            'database.connections.mysql.database' => 'missing',
            'database.connections.mysql.username' => 'missing',
            'database.connections.mysql.password' => 'missing',
        ]);

        try {
            $this->getJson('/api/v1/setup/status')
                ->assertOk()
                ->assertJsonPath('installed', false);

            $this->getJson('/api/v1/setup/logs')
                ->assertOk()
                ->assertJsonPath('logs', []);
        } finally {
            config(['database.connections.mysql' => $originalMysql]);
            config(['database.default' => 'sqlite']);

            if ($hadMarker) {
                file_put_contents($marker, '{"version":"test-restore"}');
            }
        }
    }

    public function test_setup_finish_installs_application_and_assigns_owner_role(): void
    {
        $this->seed(\App\Modules\Users\Database\Seeders\RolesAndPermissionsSeeder::class);

        $state = app(InstallationStateService::class);
        $installPath = $state->installScriptPath();

        try {
            $this->postJson('/api/v1/setup/finish', [
                'site_title' => 'Test Site',
                'admin_email' => 'setup@luma.test',
                'admin_password' => 'secure-owner-pass',
                'with_starter_site' => true,
            ])
                ->assertOk()
                ->assertJsonPath('ok', true);

            $this->assertTrue($state->isInstalled());
            $this->assertFileExists($state->installedMarkerPath());
            $this->assertFileDoesNotExist($installPath);
            $this->assertDatabaseHas('users', ['email' => 'setup@luma.test']);
            $this->assertDatabaseHas('pages', ['slug' => 'home']);

            $user = \App\Models\User::query()->where('email', 'setup@luma.test')->firstOrFail();
            $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
            $this->assertTrue($user->roles()->where('roles.id', $ownerRole->id)->exists());
        } finally {
            SharedHostingInstallScript::restore();
        }
    }

    public function test_web_root_redirects_to_setup_when_not_installed(): void
    {
        $this->get('/')
            ->assertRedirect('/admin/setup');
    }

    public function test_setup_routes_are_locked_after_install(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.22-dev',
        ]);

        $this->getJson('/api/v1/setup/requirements')->assertForbidden();
    }

    public function test_setup_write_routes_are_rate_limited(): void
    {
        config(['luma.setup_token' => '']);

        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/api/v1/setup/database/test', [
                'driver' => 'sqlite',
                'database' => database_path('database.sqlite'),
            ])->assertOk();
        }

        $this->postJson('/api/v1/setup/database/test', [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ])->assertStatus(429);
    }

    public function test_setup_token_is_required_when_configured(): void
    {
        config(['luma.setup_token' => 'test-setup-token']);

        $this->postJson('/api/v1/setup/database/test', [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ])->assertForbidden();

        $this->postJson(
            '/api/v1/setup/database/test',
            [
                'driver' => 'sqlite',
                'database' => database_path('database.sqlite'),
            ],
            ['X-Luma-Setup-Token' => 'test-setup-token'],
        )->assertOk();
    }

    public function test_setup_requirements_work_without_token_when_not_configured(): void
    {
        config(['luma.setup_token' => '']);

        $this->getJson('/api/v1/setup/requirements')
            ->assertOk()
            ->assertJsonStructure(['passed', 'checks']);
    }

    public function test_setup_rejects_invalid_setup_token(): void
    {
        config(['luma.setup_token' => 'expected-token']);

        $this->getJson('/api/v1/setup/requirements', [
            'X-Luma-Setup-Token' => 'wrong-token',
        ])->assertForbidden();
    }

    public function test_production_rejects_weak_admin_password(): void
    {
        $this->seed(\App\Modules\Users\Database\Seeders\RolesAndPermissionsSeeder::class);

        config(['luma.enforce_strong_passwords' => true]);

        $this->postJson('/api/v1/setup/finish', [
            'site_title' => 'Prod Site',
            'admin_email' => 'prod@luma.test',
            'admin_password' => 'password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['admin_password']);
    }
}
