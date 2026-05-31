<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Middleware;

use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Services\CapabilityGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePluginRouteCapability
{
    public function __construct(
        private readonly CapabilityGate $capabilityGate,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $plugin = $request->route('plugin');

        if (! $plugin instanceof Plugin) {
            return response()->json(['message' => 'Plugin not found.'], 404);
        }

        if ($plugin->status !== PluginStatus::Enabled) {
            return response()->json(['message' => 'Plugin is not enabled.'], 403);
        }

        $plugin->loadMissing('capabilities');

        if (! $this->capabilityGate->allows($plugin, 'routes.register')) {
            return response()->json(['message' => 'Plugin route capability is not granted.'], 403);
        }

        return $next($request);
    }
}
