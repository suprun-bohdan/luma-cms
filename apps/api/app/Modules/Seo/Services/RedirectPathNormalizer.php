<?php

declare(strict_types=1);

namespace App\Modules\Seo\Services;

final class RedirectPathNormalizer
{
    public static function normalize(string $path): string
    {
        $path = '/'.trim($path, '/');

        if ($path === '/') {
            return '/';
        }

        return rtrim($path, '/');
    }
}
