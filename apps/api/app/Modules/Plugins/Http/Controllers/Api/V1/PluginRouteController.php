<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Services\PluginRouteRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PluginRouteController extends Controller
{
    public function __construct(
        private readonly PluginRouteRegistry $routes,
    ) {
    }

    public function __invoke(Request $request, Plugin $plugin, ?string $pluginPath = null): JsonResponse
    {
        $handler = $this->routes->match(
            $plugin->plugin_id,
            $request->method(),
            $pluginPath ?? '',
        );

        if ($handler === null) {
            return response()->json(['message' => 'Plugin route not found.'], 404);
        }

        $result = ($handler)($request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json($result);
    }
}
