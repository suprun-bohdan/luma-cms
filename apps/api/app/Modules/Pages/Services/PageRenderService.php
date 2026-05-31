<?php

declare(strict_types=1);

namespace App\Modules\Pages\Services;

use App\Modules\Media\Models\Media;
use App\Modules\Media\Services\MediaStorageService;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Rendering\BlockRendererRegistry;
use Illuminate\Support\Facades\View;

final class PageRenderService
{
    public function __construct(
        private readonly BlockRendererRegistry $blocks,
        private readonly MediaStorageService $mediaStorage,
    ) {
    }

    /**
     * @param  array<string, mixed>|null  $contentOverride
     * @param  array<string, mixed>|null  $seoOverride
     */
    public function renderHtml(
        Page $page,
        ?array $contentOverride = null,
        ?array $seoOverride = null,
        ?string $titleOverride = null,
    ): string {
        $content = $contentOverride ?? (is_array($page->content) ? $page->content : ['blocks' => []]);
        $blocks = is_array($content['blocks'] ?? null) ? $content['blocks'] : [];

        $renderedBlocks = [];
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $html = $this->blocks->render($block);
            if ($html !== '') {
                $renderedBlocks[] = $html;
            }
        }

        $seo = $seoOverride ?? (is_array($page->seo) ? $page->seo : []);
        $pageTitle = is_string($titleOverride) && $titleOverride !== '' ? $titleOverride : $page->title;
        $metaTitle = is_string($seo['title'] ?? null) && $seo['title'] !== ''
            ? $seo['title']
            : $pageTitle;
        $metaDescription = is_string($seo['description'] ?? null) ? $seo['description'] : '';
        $ogImage = $this->resolveOgImageUrl($seo['og_image'] ?? null);

        $menu = Menu::query()->where('slug', 'header')->with('items')->first();
        $menuItems = $menu?->items ?? collect();

        $footerMenu = Menu::query()->where('slug', 'footer')->with('items')->first();
        $footerMenuItems = $footerMenu?->items ?? collect();

        $canonicalUrl = url('/p/'.$page->slug);

        return View::make('pages.show', [
            'page' => $page,
            'blocks' => $renderedBlocks,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'ogImage' => $ogImage,
            'canonicalUrl' => $canonicalUrl,
            'menuItems' => $menuItems,
            'footerMenuItems' => $footerMenuItems,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $content
     * @param  array<string, mixed>|null  $seo
     */
    public function renderDraftHtml(string $slug, string $title, array $content, ?array $seo = null): string
    {
        $page = new Page([
            'slug' => $slug,
            'title' => $title,
            'template' => 'default-page',
            'content' => $content,
            'seo' => $seo,
        ]);

        return $this->renderHtml($page, $content, $seo, $title);
    }

    private function resolveOgImageUrl(mixed $uuid): ?string
    {
        if (! is_string($uuid) || $uuid === '') {
            return null;
        }

        $media = Media::query()->where('uuid', $uuid)->first();
        if ($media === null) {
            return null;
        }

        return $this->mediaStorage->url($media->disk, $media->path);
    }
}
