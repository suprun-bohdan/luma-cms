<?php

declare(strict_types=1);

/**
 * Pre-bootstrap PHP gate for shared hosting (no Composer required).
 * Open directly after upload or link from INSTALL.txt.
 */
$installedMarker = __DIR__.'/../storage/app/.luma-installed';

if (is_file($installedMarker)) {
    header('Location: /admin/login', true, 302);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Luma CMS</title></head><body>';
    echo '<p>Luma CMS is already installed. Continue to <a href="/admin/login">/admin/login</a>.</p>';
    echo '</body></html>';

    exit(0);
}

$requirements = require __DIR__.'/../bootstrap/luma-requirements.php';
$minimum = $requirements['php']['minimum'] ?? '8.3.0';
$recommended = $requirements['php']['recommended'] ?? '8.4.0';
/** @var list<string> $supportedBranches */
$supportedBranches = $requirements['php']['supported_branches'] ?? ['8.3', '8.4'];
$current = PHP_VERSION;

$accept = isset($_GET['format']) && $_GET['format'] === 'json'
    ? 'application/json; charset=utf-8'
    : 'text/html; charset=utf-8';

header('Content-Type: '.$accept);

$parts = explode('.', $current);
$branch = ($parts[0] ?? '0').'.'.($parts[1] ?? '0');
$meetsMinimum = version_compare($current, $minimum, '>=');
$supportedBranch = in_array($branch, $supportedBranches, true);

$payload = [
    'current' => $current,
    'minimum' => $minimum,
    'recommended' => $recommended,
    'supported_branches' => $supportedBranches,
    'passed' => $meetsMinimum,
    'supported_branch' => $supportedBranch,
];

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    exit($meetsMinimum ? 0 : 1);
}

if ($meetsMinimum) {
    header('Location: /admin/setup', true, 302);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Luma CMS</title></head><body>';
    echo '<p>PHP '.$current.' is supported. Continue to <a href="/admin/setup">/admin/setup</a>.</p>';
    echo '</body></html>';

    exit(0);
}

http_response_code(500);

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Luma CMS — PHP requirement</title>';
echo '<style>body{font-family:system-ui,sans-serif;max-width:640px;margin:2rem auto;line-height:1.5}</style>';
echo '</head><body>';
echo '<h1>PHP version too old</h1>';
echo '<p>Luma CMS requires <strong>PHP '.$minimum.'</strong> or newer (Laravel 13).</p>';
echo '<p>Your server is running <strong>PHP '.$current.'</strong>.</p>';
echo '<p>PHP 7.x and 8.0–8.2 are not supported. In cPanel or your hosting panel, select <strong>PHP 8.3</strong> or <strong>8.4</strong> for this domain, then reload this page.</p>';
echo '<p>Supported legacy branches: '.implode(', ', $supportedBranches).'.</p>';
echo '</body></html>';

exit(1);
