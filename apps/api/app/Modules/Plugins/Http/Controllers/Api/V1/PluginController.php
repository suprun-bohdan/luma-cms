<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Plugins\Http\Requests\ApprovePluginCapabilityRequest;
use App\Modules\Plugins\Http\Requests\InstallPluginRequest;
use App\Modules\Plugins\Http\Resources\AuditLogResource;
use App\Modules\Plugins\Http\Resources\PluginResource;
use App\Modules\Plugins\Models\AuditLog;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Services\PluginLifecycleService;
use App\Modules\Plugins\Services\PluginRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;
use RuntimeException;

final class PluginController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Plugin::class);

        $plugins = Plugin::query()->with('capabilities')->orderBy('name')->get();

        return PluginResource::collection($plugins);
    }

    public function discover(PluginRegistryService $registry): JsonResponse
    {
        $this->authorize('viewAny', Plugin::class);

        return response()->json([
            'data' => $registry->discover(),
        ]);
    }

    public function install(
        InstallPluginRequest $request,
        PluginLifecycleService $lifecycle,
    ): JsonResponse {
        $this->authorize('install', Plugin::class);

        try {
            $plugin = $lifecycle->install($request->validated('plugin_id'), $request->user());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return (new PluginResource($plugin))
            ->response()
            ->setStatusCode(201);
    }

    public function enable(Plugin $plugin, PluginLifecycleService $lifecycle): PluginResource|JsonResponse
    {
        $this->authorize('enable', $plugin);

        try {
            return new PluginResource($lifecycle->enable($plugin, request()->user()));
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function disable(Plugin $plugin, PluginLifecycleService $lifecycle): PluginResource
    {
        $this->authorize('disable', $plugin);

        return new PluginResource($lifecycle->disable($plugin, request()->user()));
    }

    public function destroy(Plugin $plugin, PluginLifecycleService $lifecycle): JsonResponse
    {
        $this->authorize('delete', $plugin);

        $lifecycle->uninstall($plugin, request()->user());

        return response()->json(null, 204);
    }

    public function approveCapability(
        ApprovePluginCapabilityRequest $request,
        Plugin $plugin,
        PluginLifecycleService $lifecycle,
    ): PluginResource|JsonResponse {
        $this->authorize('approveCapability', $plugin);

        try {
            return new PluginResource(
                $lifecycle->approveCapability(
                    $plugin,
                    $request->validated('capability'),
                    $request->user(),
                ),
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function auditLogs(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::query()
            ->with('actor')
            ->orderByDesc('id')
            ->paginate(50);

        return AuditLogResource::collection($logs);
    }
}
