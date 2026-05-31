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

if (! is_file($installedMarker)) {
    if (is_file($installScript)) {
        header('Location: install.php', true, 302);
        exit;
    }

    header('Location: /admin/setup', true, 302);
    exit;
}

$env = $apiRoot.'/.env';
$example = $apiRoot.'/.env.shared.example';

if (! is_file($env) && is_file($example) && is_writable($apiRoot)) {
    @copy($example, $env);
}

require $apiRoot.'/public/index.php';
