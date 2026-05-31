<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Middleware;

use App\Modules\Integrations\Services\IntegrationTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticateIntegrationToken
{
    public function __construct(
        private readonly IntegrationTokenService $tokens,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->bearerToken();

        if ($header === null || $header === '') {
            return response()->json(['message' => 'Integration token required.'], 401);
        }

        $token = $this->tokens->findByPlainText($header);

        if ($token === null) {
            return response()->json(['message' => 'Invalid integration token.'], 401);
        }

        $request->attributes->set('integration_token', $token);

        return $next($request);
    }
}
