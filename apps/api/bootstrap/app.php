<?php

use Illuminate\Foundation\Application;
use App\Modules\Seo\Http\Middleware\RedirectMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(RedirectMiddleware::class);

        $middleware->redirectGuestsTo(function (Request $request): ?string {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return null;
        });

        $middleware->alias([
            'integration.token' => \App\Modules\Integrations\Http\Middleware\AuthenticateIntegrationToken::class,
            'integration.scope' => \App\Modules\Integrations\Http\Middleware\EnsureIntegrationTokenScope::class,
            'luma.not_installed' => \App\Modules\Setup\Http\Middleware\EnsureNotInstalled::class,
            'luma.setup_token' => \App\Modules\Setup\Http\Middleware\EnsureSetupToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
