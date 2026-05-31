<?php

declare(strict_types=1);

namespace App\Modules\Setup\Http\Middleware;

use App\Modules\Setup\Services\InstallationStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureNotInstalled
{
    public function __construct(
        private readonly InstallationStateService $installationState,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->installationState->isInstalled()) {
            return response()->json([
                'message' => 'Luma CMS is already installed.',
            ], 403);
        }

        return $next($request);
    }
}
