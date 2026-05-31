<?php

declare(strict_types=1);

return [
    'path' => env('LUMA_PLUGINS_PATH', dirname(base_path(), 2).DIRECTORY_SEPARATOR.'plugins'),
    'manifest_filename' => 'luma.plugin.json',
    'luma_version' => '0.1.0',
    'plugin_api_version' => '1.0',
];
