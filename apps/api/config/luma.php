<?php

$requirements = require __DIR__.'/../bootstrap/luma-requirements.php';

return array_merge($requirements, [
    'version' => env('LUMA_VERSION', $requirements['version'] ?? '0.0.25-rc.4'),
    'skip_onboarding' => env('LUMA_SKIP_ONBOARDING', false),
    'setup_token' => env('LUMA_SETUP_TOKEN', ''),
    'enforce_strong_passwords' => env('LUMA_ENFORCE_STRONG_PASSWORDS', false),
]);
