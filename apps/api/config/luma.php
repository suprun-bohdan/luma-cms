<?php

$requirements = require __DIR__.'/../bootstrap/luma-requirements.php';

return array_merge($requirements, [
    'version' => env('LUMA_VERSION', $requirements['version'] ?? '0.0.22-dev'),
    'skip_onboarding' => env('LUMA_SKIP_ONBOARDING', false),
]);
