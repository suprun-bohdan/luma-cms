<?php

declare(strict_types=1);

/**
 * Shared-hosting front controller when document root is the archive root.
 * Boots Laravel from apps/api/public without changing panel document root.
 */
$apiRoot = __DIR__.'/apps/api';
$env = $apiRoot.'/.env';
$example = $apiRoot.'/.env.shared.example';

if (! is_file($env) && is_file($example) && is_writable($apiRoot)) {
    @copy($example, $env);
}

require __DIR__.'/apps/api/public/index.php';
