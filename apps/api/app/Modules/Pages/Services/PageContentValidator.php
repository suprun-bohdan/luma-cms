<?php

declare(strict_types=1);

namespace App\Modules\Pages\Services;

use Illuminate\Validation\ValidationException;

final class PageContentValidator
{
    private const ALLOWED_TYPES = ['hero', 'rich_text', 'cta', 'contact_form'];

    /**
     * @param  array<string, mixed>|null  $content
     * @return array<string, mixed>
     */
    public function validate(?array $content): array
    {
        if ($content === null) {
            return ['blocks' => []];
        }

        if (! isset($content['blocks']) || ! is_array($content['blocks'])) {
            throw ValidationException::withMessages([
                'content' => ['Content must include a blocks array.'],
            ]);
        }

        $blocks = [];

        foreach ($content['blocks'] as $index => $block) {
            if (! is_array($block)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}" => ['Each block must be an object.'],
                ]);
            }

            $type = $block['type'] ?? null;
            $id = $block['id'] ?? null;

            if (! is_string($type) || ! in_array($type, self::ALLOWED_TYPES, true)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.type" => ['Block type must be one of: '.implode(', ', self::ALLOWED_TYPES).'.'],
                ]);
            }

            if (! is_string($id) || $id === '') {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.id" => ['Block id is required.'],
                ]);
            }

            $props = $block['props'] ?? [];
            if (! is_array($props)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.props" => ['Block props must be an object.'],
                ]);
            }

            $normalized = [
                'id' => $id,
                'type' => $type,
                'props' => $props,
            ];

            if (isset($block['variant']) && is_string($block['variant'])) {
                $normalized['variant'] = $block['variant'];
            }

            if ($type === 'contact_form') {
                $formSlug = $props['form_slug'] ?? null;
                if (! is_string($formSlug) || $formSlug === '') {
                    throw ValidationException::withMessages([
                        "content.blocks.{$index}.props.form_slug" => ['Contact form block requires a form_slug prop.'],
                    ]);
                }
            }

            $blocks[] = $normalized;
        }

        return ['blocks' => $blocks];
    }
}
