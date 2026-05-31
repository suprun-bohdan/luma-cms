<?php

declare(strict_types=1);

namespace App\Modules\Setup\Http\Middleware;

use App\Modules\Setup\Services\InstallationStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSetupToken
{
    public function __construct(
        private readonly InstallationStateService $installationState,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('luma.setup_token', '');

        if ($token === '' || $this->installationState->isInstalled()) {
            return $next($request);
        }

        $provided = (string) $request->header('X-Luma-Setup-Token', '');

        if (! hash_equals($token, $provided)) {
            return response()->json([
                'message' => 'Invalid or missing setup token.',
            ], 403);
        }

        return $next($request);
    }
}
