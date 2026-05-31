<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Http\Controllers\Api\V1\PluginRouteController;
use App\Modules\Plugins\Http\Middleware\EnsurePluginRouteCapability;
use Illuminate\Support\Facades\Route;

final class PluginRouteRegistrar
{
    public function registerRoutes(): void
    {
        Route::middleware(['api', 'auth:sanctum', EnsurePluginRouteCapability::class])
            ->match(
                ['GET', 'POST', 'PUT', 'DELETE'],
                'api/v1/plugins/{plugin:plugin_id}/{pluginPath?}',
                PluginRouteController::class,
            )
            ->where('pluginPath', '.*');
    }
}
