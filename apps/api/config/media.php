<?php

declare(strict_types=1);

return [
    'disk' => env('MEDIA_DISK', 'public'),
    'directory' => 'media',
    'max_upload_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 5120),
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
    ],
    'thumbnail_width' => 400,
];
