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

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
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

            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $driver = $config['driver'];

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

        $envFileWriter->merge(base_path('.env'), $envValues);
        Artisan::call('config:clear');

        $setupLog->write('database.save', 'success', 'Database settings saved to .env');

        return response()->json(['ok' => true]);
    }

    public function finish(
        SetupFinishRequest $request,
        InstallService $installService,
        EnvFileWriter $envFileWriter,
    ): JsonResponse {
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
            ));
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'redirect' => '/studio/login',
        ]);
    }
}
