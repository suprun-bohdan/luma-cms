<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\System\Models\SystemUpdate;
use App\Core\Update\SystemVersionService;
use App\Core\Update\UpdateService;
use App\Http\Controllers\Controller;
use App\Modules\Plugins\Services\AuditLogService;
use App\Modules\Setup\Services\InstallationStateService;
use App\Modules\Setup\Services\SetupLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class SystemController extends Controller
{
    public function version(SystemVersionService $versions): JsonResponse
    {
        return response()->json([
            'data' => $versions->current(),
        ]);
    }

    public function updateCheck(SystemVersionService $versions): JsonResponse
    {
        $this->authorize('check', SystemUpdate::class);

        return response()->json([
            'data' => $versions->checkForUpdates(),
        ]);
    }

    public function updateRun(
        Request $request,
        InstallationStateService $installationState,
        UpdateService $updateService,
        SetupLogService $setupLog,
        AuditLogService $auditLog,
    ): JsonResponse {
        $this->authorize('run', SystemUpdate::class);

        if (! $installationState->isInstalled()) {
            return response()->json([
                'ok' => false,
                'message' => 'Luma CMS is not installed yet.',
            ], 422);
        }

        $auditLog->record(
            'system.update.started',
            'system',
            'luma',
            $request->user(),
            [
                'version' => config('luma.version'),
                'ip' => $request->ip(),
            ],
        );

        try {
            $result = $updateService->run();
            $setupLog->write('system.update', 'success', 'Database migrations and caches refreshed after release.');
            $auditLog->record(
                'system.update.succeeded',
                'system',
                'luma',
                $request->user(),
                [
                    'version' => config('luma.version'),
                    'ip' => $request->ip(),
                ],
            );

            return response()->json([
                'ok' => true,
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            $setupLog->write('system.update', 'error', $exception->getMessage());
            $auditLog->record(
                'system.update.failed',
                'system',
                'luma',
                $request->user(),
                [
                    'version' => config('luma.version'),
                    'ip' => $request->ip(),
                    'message' => $exception->getMessage(),
                ],
            );

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
