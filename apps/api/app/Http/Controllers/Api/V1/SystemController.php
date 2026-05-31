<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Update\SystemVersionService;
use App\Core\Update\UpdateService;
use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Setting;
use App\Modules\Setup\Services\InstallationStateService;
use App\Modules\Setup\Services\SetupLogService;
use Illuminate\Http\JsonResponse;
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
        $this->authorize('viewAny', Setting::class);

        return response()->json([
            'data' => $versions->checkForUpdates(),
        ]);
    }

    public function updateRun(
        InstallationStateService $installationState,
        UpdateService $updateService,
        SetupLogService $setupLog,
    ): JsonResponse {
        $this->authorize('viewAny', Setting::class);

        if (! $installationState->isInstalled()) {
            return response()->json([
                'ok' => false,
                'message' => 'Luma CMS is not installed yet.',
            ], 422);
        }

        try {
            $result = $updateService->run();
            $setupLog->write('system.update', 'success', 'Database migrations and caches refreshed after release.');

            return response()->json([
                'ok' => true,
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            $setupLog->write('system.update', 'error', $exception->getMessage());

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
