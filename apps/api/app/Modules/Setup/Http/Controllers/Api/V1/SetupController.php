<?php

declare(strict_types=1);

namespace App\Modules\Setup\Http\Controllers\Api\V1;

use App\Core\Install\InstallOptions;
use App\Core\Install\InstallService;
use App\Core\Requirements\EnvironmentRequirementChecker;
use App\Http\Controllers\Controller;
use App\Modules\Setup\Http\Requests\SetupDatabaseRequest;
use App\Modules\Setup\Http\Requests\SetupFinishRequest;
use App\Modules\Setup\Services\EnvFileWriter;
use App\Modules\Setup\Services\InstallationStateService;
use App\Modules\Setup\Services\SetupLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Throwable;

final class SetupController extends Controller
{
    public function status(InstallationStateService $installationState): JsonResponse
    {
        return response()->json([
            'installed' => $installationState->isInstalled(),
            'version' => (string) config('luma.version', '0.0.0-dev'),
        ]);
    }

    public function requirements(EnvironmentRequirementChecker $requirements): JsonResponse
    {
        $report = $requirements->check();

        return response()->json([
            'passed' => $report->passed(),
            'checks' => $report->toArray(),
        ]);
    }

    public function logs(SetupLogService $setupLog): JsonResponse
    {
        return response()->json([
            'logs' => $setupLog->recent(),
        ]);
    }

    public function testDatabase(
        SetupDatabaseRequest $request,
        InstallService $installService,
        SetupLogService $setupLog,
    ): JsonResponse {
        try {
            $installService->testDatabaseConnection($request->databaseConfig());
            $setupLog->write('database.test', 'success', 'Database connection successful.');

            return response()->json(['ok' => true]);
        } catch (Throwable $exception) {
            $setupLog->write('database.test', 'error', $exception->getMessage());

            return $this->setupError(
                message: $exception->getMessage(),
                messageKey: 'errors.database.connectionFailed',
            );
        }
    }

    public function saveDatabase(
        SetupDatabaseRequest $request,
        InstallService $installService,
        EnvFileWriter $envFileWriter,
        SetupLogService $setupLog,
    ): JsonResponse {
        $config = $request->databaseConfig();

        try {
            $installService->testDatabaseConnection($config);
        } catch (Throwable $exception) {
            $setupLog->write('database.save', 'error', $exception->getMessage());

            return $this->setupError(
                message: $exception->getMessage(),
                messageKey: 'errors.database.connectionFailed',
            );
        }

        $driver = $config['driver'];
        $envPath = base_path('.env');
        $envExample = base_path('.env.shared.example');

        if (! is_file($envPath) && is_file($envExample)) {
            if (! @copy($envExample, $envPath)) {
                return $this->setupError(
                    message: 'Unable to create apps/api/.env. Make the apps/api folder writable by the web server.',
                    messageKey: 'errors.database.envNotWritable',
                );
            }
        }

        $envValues = [
            'DB_CONNECTION' => $driver,
        ];

        if ($driver === 'sqlite') {
            $envValues['DB_DATABASE'] = $config['database'];
        } else {
            $envValues['DB_HOST'] = $config['host'] ?? '';
            $envValues['DB_PORT'] = (string) ($config['port'] ?? '');
            $envValues['DB_DATABASE'] = $config['database'] ?? '';
            $envValues['DB_USERNAME'] = $config['username'] ?? '';
            $envValues['DB_PASSWORD'] = $config['password'] ?? '';
        }

        try {
            $envFileWriter->merge($envPath, $envValues);
            Artisan::call('config:clear');
        } catch (Throwable $exception) {
            $setupLog->write('database.save', 'error', $exception->getMessage());

            return $this->setupError(
                message: $exception->getMessage(),
                messageKey: 'errors.database.envMergeFailed',
            );
        }

        $setupLog->write('database.save', 'success', 'Database settings saved to .env');

        return response()->json(['ok' => true]);
    }

    public function finish(
        SetupFinishRequest $request,
        InstallService $installService,
        EnvFileWriter $envFileWriter,
        InstallationStateService $installationState,
    ): JsonResponse {
        if ($installationState->isInstalled()) {
            return $this->setupError(
                message: 'Luma CMS is already installed.',
                messageKey: 'errors.finish.alreadyInstalled',
            );
        }

        if ($request->filled('site_title')) {
            $envFileWriter->merge(base_path('.env'), [
                'APP_NAME' => $request->string('site_title')->toString(),
            ]);
            Artisan::call('config:clear');
        }

        try {
            $installService->install(new InstallOptions(
                adminEmail: $request->string('admin_email')->toString(),
                adminPassword: $request->string('admin_password')->toString(),
                withStarterSite: $request->boolean('with_starter_site', true),
                siteTitle: $request->input('site_title'),
                allowWeakPassword: $request->boolean('allow_weak_password'),
            ));
        } catch (Throwable $exception) {
            return $this->setupError(
                message: $exception->getMessage(),
                messageKey: 'errors.finish.installFailed',
            );
        }

        return response()->json([
            'ok' => true,
            'redirect' => '/admin/login',
        ]);
    }

    /**
     * @param  array<string, string|int|float>  $messageParams
     */
    private function setupError(
        string $message,
        string $messageKey,
        array $messageParams = [],
        int $status = 422,
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'message_key' => $messageKey,
            'message_params' => $messageParams === [] ? new \stdClass() : $messageParams,
        ], $status);
    }
}
