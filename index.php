<?php

declare(strict_types=1);

/**
 * Luma CMS public front controller (shared hosting document root = project root).
 * Redirects fresh installs to install.php; runs Laravel when installed.
 */
$projectRoot = __DIR__;
$apiRoot = $projectRoot.'/apps/api';
$installedMarker = $apiRoot.'/storage/app/.luma-installed';
$installScript = $projectRoot.'/install.php';
$studioDist = $projectRoot.'/apps/studio/dist';

/**
 * @return non-empty-string
 */
function luma_request_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);

    if (! is_string($path) || $path === '') {
        return '/';
    }

    return $path;
}

function luma_bootstrap_api(string $apiRoot): void
{
    $env = $apiRoot.'/.env';
    $example = $apiRoot.'/.env.shared.example';

    if (! is_file($env) && is_file($example) && is_writable($apiRoot)) {
        @copy($example, $env);
    }

    require $apiRoot.'/public/index.php';
}

function luma_serve_studio(string $studioDist, string $path): bool
{
    if (! str_starts_with($path, '/admin')) {
        return false;
    }

    $relative = substr($path, strlen('/admin'));
    if ($relative === '' || $relative === '/') {
        $relative = '/index.html';
    }

    $candidate = $studioDist.$relative;
    if (is_file($candidate)) {
        $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        $types = [
            'html' => 'text/html; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'css' => 'text/css; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'json' => 'application/json; charset=UTF-8',
            'ico' => 'image/x-icon',
        ];

        header('Content-Type: '.($types[$extension] ?? 'application/octet-stream'), true);
        readfile($candidate);

        return true;
    }

    $index = $studioDist.'/index.html';
    if (! is_file($index)) {
        return false;
    }

    header('Content-Type: text/html; charset=UTF-8', true);
    readfile($index);

    return true;
}

$path = luma_request_path();

// Studio admin UI (/admin/*) is static SPA — serve before Laravel on every request.
// Required when nginx has no location ^~ /admin/ block (common on BrainyCP panels).
if (str_starts_with($path, '/admin') && luma_serve_studio($studioDist, $path)) {
    exit;
}

if (! is_file($installedMarker)) {
    // Setup API and health checks must reach Laravel before install completes.
    if (str_starts_with($path, '/api/') || $path === '/up') {
        luma_bootstrap_api($apiRoot);
        exit;
    }

    if (is_file($installScript)) {
        header('Location: /install.php', true, 302);
        exit;
    }

    header('Location: /admin/setup', true, 302);
    exit;
}

luma_bootstrap_api($apiRoot);
