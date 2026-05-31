<?php

declare(strict_types=1);

/**
 * Luma CMS installer entry (OpenCart / WordPress style).
 * Removed automatically after successful setup. UI: /admin/setup
 */
$projectRoot = __DIR__;
$installedMarker = $projectRoot.'/apps/api/storage/app/.luma-installed';

if (is_file($installedMarker)) {
    header('Location: /admin/login', true, 302);
    exit;
}

$apiRoot = $projectRoot.'/apps/api';
$env = $apiRoot.'/.env';
$example = $apiRoot.'/.env.shared.example';

if (! is_file($env) && is_file($example) && is_writable($apiRoot)) {
    @copy($example, $env);
}

header('Location: /admin/setup', true, 302);
exit;