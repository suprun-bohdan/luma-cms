<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Media\Models\Media;
use App\Modules\Media\Services\MediaStorageService;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Rendering\BlockRendererRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

final class PublicPageViewController extends Controller
{
    public function __construct(
        private readonly BlockRendererRegistry $blocks,
        private readonly MediaStorageService $mediaStorage,
    ) {
    }

    public function show(Page $page): View|Response
    {
        if ($page->status !== PageStatus::Published) {
            abort(404);
        }

        $content = is_array($page->content) ? $page->content : ['blocks' => []];
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

        $seo = is_array($page->seo) ? $page->seo : [];
        $metaTitle = is_string($seo['title'] ?? null) && $seo['title'] !== ''
            ? $seo['title']
            : $page->title;
        $metaDescription = is_string($seo['description'] ?? null) ? $seo['description'] : '';
        $ogImage = $this->resolveOgImageUrl($seo['og_image'] ?? null);

        $menu = Menu::query()->where('slug', 'header')->with('items')->first();
        $menuItems = $menu?->items ?? collect();

        return view('pages.show', [
            'page' => $page,
            'blocks' => $renderedBlocks,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'ogImage' => $ogImage,
            'menuItems' => $menuItems,
        ]);
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
