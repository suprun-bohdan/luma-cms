<?php

declare(strict_types=1);

namespace App\Modules\Seo\Services;

use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Support\Collection;

final class SitemapGenerator
{
    public function generateXml(): string
    {
        $pages = Page::query()
            ->where('status', PageStatus::Published)
            ->orderBy('slug')
            ->get(['slug', 'updated_at', 'published_at']);

        return $this->buildXml($pages);
    }

    /**
     * @param  Collection<int, Page>  $pages
     */
    private function buildXml(Collection $pages): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($pages as $page) {
            $lastMod = ($page->published_at ?? $page->updated_at)?->toAtomString();
            $loc = e(url('/p/'.$page->slug));

            $lines[] = '  <url>';
            $lines[] = '    <loc>'.$loc.'</loc>';
            if ($lastMod !== null) {
                $lines[] = '    <lastmod>'.$lastMod.'</lastmod>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
