<?php

declare(strict_types=1);

/**
 * Friendly installer entry URL for shared hosting file managers.
 * Canonical install UI remains /admin/setup (Luma Studio wizard).
 */
$installedMarker = __DIR__.'/apps/api/storage/app/.luma-installed';

if (is_file($installedMarker)) {
    header('Location: /admin/login', true, 302);
} else {
    header('Location: /admin/setup', true, 302);
}

exit;
