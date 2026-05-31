<?php

declare(strict_types=1);

namespace App\Modules\Seo\Services;

final class RobotsTxtBuilder
{
    public function build(): string
    {
        $sitemapUrl = url('/sitemap.xml');

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /api/',
            '',
            'Sitemap: '.$sitemapUrl,
            '',
        ]);
    }
}
