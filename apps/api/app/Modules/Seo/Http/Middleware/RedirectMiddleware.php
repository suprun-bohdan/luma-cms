<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Middleware;

use App\Modules\Seo\Models\Redirect;
use App\Modules\Seo\Services\RedirectPathNormalizer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class RedirectMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() === false) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->is('up')) {
            return $next($request);
        }

        $path = RedirectPathNormalizer::normalize('/'.$request->path());

        if (in_array($path, ['/sitemap.xml', '/robots.txt'], true)) {
            return $next($request);
        }

        if (! Schema::hasTable('redirects')) {
            return $next($request);
        }

        $redirect = Redirect::query()
            ->where('is_active', true)
            ->where('from_path', $path)
            ->first();

        if ($redirect === null) {
            return $next($request);
        }

        $target = $redirect->resolveTargetUrl();

        if ($redirect->to_url !== null && $redirect->to_url !== '') {
            return redirect()->away($target, $redirect->status_code);
        }

        return redirect()->to($redirect->to_path ?? '/', $redirect->status_code);
    }
}
