<?php

/**
 * Single source of truth for Luma runtime requirements.
 * Loaded by config/luma.php and public/luma-requirements.php (no Composer).
 */
return [
    'php' => [
        // Laravel 13 + Luma lock; PHP 7.x / 8.0–8.2 are not supported.
        'minimum' => '8.3.0',
        'recommended' => '8.4.0',
        // Supported legacy branches on cheap shared hosting (panel selector).
        'supported_branches' => ['8.3', '8.4'],
    ],
    'extensions' => [
        'required' => [
            'bcmath',
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'xml',
            'zip',
            'gd',
            'intl',
        ],
    ],
    'paths' => [
        'writable' => [
            'storage',
            'bootstrap/cache',
            'database',
        ],
    ],

    'version' => '0.0.25-rc.10',
];
