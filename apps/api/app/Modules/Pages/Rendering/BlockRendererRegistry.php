<?php

declare(strict_types=1);

namespace App\Modules\Pages\Rendering;

use App\Modules\Forms\Models\Form;
use App\Modules\Media\Models\Media;
use App\Modules\Media\Services\MediaStorageService;
use Illuminate\Support\Facades\View;

final class BlockRendererRegistry
{
    public function __construct(
        private readonly MediaStorageService $mediaStorage,
    ) {
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function render(array $block): string
    {
        $type = is_string($block['type'] ?? null) ? $block['type'] : '';
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];

        return match ($type) {
            'hero' => $this->renderHero($props),
            'rich_text' => $this->renderRichText($props),
            'cta' => $this->renderCta($props),
            'contact_form' => $this->renderContactForm($props),
            'feature_grid' => $this->renderFeatureGrid($props),
            'faq' => $this->renderFaq($props),
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderHero(array $props): string
    {
        $headline = e(is_string($props['headline'] ?? null) ? $props['headline'] : '');
        $subheadline = e(is_string($props['subheadline'] ?? null) ? $props['subheadline'] : '');
        $imageUrl = $this->resolveMediaUrl($props['image_uuid'] ?? null);

        return View::make('pages.blocks.hero', [
            'headline' => $headline,
            'subheadline' => $subheadline,
            'imageUrl' => $imageUrl,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderRichText(array $props): string
    {
        $body = is_string($props['body'] ?? null) ? $props['body'] : '';

        return View::make('pages.blocks.rich_text', [
            'body' => nl2br(e($body)),
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderCta(array $props): string
    {
        $label = e(is_string($props['label'] ?? null) ? $props['label'] : 'Learn more');
        $url = e(is_string($props['url'] ?? null) ? $props['url'] : '#');

        return View::make('pages.blocks.cta', [
            'label' => $label,
            'url' => $url,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderContactForm(array $props): string
    {
        $formSlug = is_string($props['form_slug'] ?? null) ? $props['form_slug'] : '';
        if ($formSlug === '') {
            return '';
        }

        $form = Form::query()
            ->where('slug', $formSlug)
            ->where('is_active', true)
            ->with('fields')
            ->first();

        if ($form === null) {
            return '';
        }

        $title = e(is_string($props['title'] ?? null) ? $props['title'] : 'Contact us');
        $submitLabel = e(is_string($props['submit_label'] ?? null) ? $props['submit_label'] : 'Send message');

        return View::make('pages.blocks.contact_form', [
            'form' => $form,
            'title' => $title,
            'submitLabel' => $submitLabel,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderFeatureGrid(array $props): string
    {
        $heading = e(is_string($props['heading'] ?? null) ? $props['heading'] : '');
        $items = [];

        foreach (is_array($props['items'] ?? null) ? $props['items'] : [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $items[] = [
                'title' => e(is_string($item['title'] ?? null) ? $item['title'] : ''),
                'body' => e(is_string($item['body'] ?? null) ? $item['body'] : ''),
            ];
        }

        return View::make('pages.blocks.feature_grid', [
            'heading' => $heading,
            'items' => $items,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderFaq(array $props): string
    {
        $heading = e(is_string($props['heading'] ?? null) ? $props['heading'] : '');
        $items = [];

        foreach (is_array($props['items'] ?? null) ? $props['items'] : [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $items[] = [
                'question' => e(is_string($item['question'] ?? null) ? $item['question'] : ''),
                'answer' => e(is_string($item['answer'] ?? null) ? $item['answer'] : ''),
            ];
        }

        return View::make('pages.blocks.faq', [
            'heading' => $heading,
            'items' => $items,
        ])->render();
    }

    private function resolveMediaUrl(mixed $uuid): ?string
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
