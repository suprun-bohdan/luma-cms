<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Middleware;

use App\Modules\Integrations\Models\IntegrationToken;
use App\Modules\Integrations\Services\IntegrationScopeCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIntegrationTokenScope
{
    public function __construct(
        private readonly IntegrationScopeCatalog $scopes,
    ) {
    }

    public function handle(Request $request, Closure $next, string $scopeKey): Response
    {
        $token = $request->attributes->get('integration_token');

        if (! $token instanceof IntegrationToken) {
            return response()->json(['message' => 'Integration token required.'], 401);
        }

        $ability = $this->scopes->abilityFromMiddlewareKey($scopeKey);

        if (! $token->hasAbility($ability)) {
            return response()->json(['message' => 'Insufficient integration token scope.'], 403);
        }

        return $next($request);
    }
}
