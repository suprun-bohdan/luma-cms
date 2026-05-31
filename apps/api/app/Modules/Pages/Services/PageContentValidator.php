<?php

declare(strict_types=1);

namespace App\Modules\Pages\Services;

use App\Modules\Pages\Support\CoreBlockDefinitions;
use App\Modules\Plugins\Services\BlockTypeService;
use App\Modules\Plugins\Support\BlockTypeDefinition;
use Illuminate\Validation\ValidationException;

final class PageContentValidator
{
    public function __construct(
        private readonly BlockTypeService $blockTypes,
    ) {
    }

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

        $allowedTypes = $this->blockTypes->allowedTypeIds();
        $blocks = [];

        foreach ($content['blocks'] as $index => $block) {
            if (! is_array($block)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}" => ['Each block must be an object.'],
                ]);
            }

            $type = $block['type'] ?? null;
            $id = $block['id'] ?? null;

            if (! is_string($type) || ! in_array($type, $allowedTypes, true)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.type" => ['Block type must be one of: '.implode(', ', $allowedTypes).'.'],
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

            if (in_array($type, CoreBlockDefinitions::ALLOWED_TYPES, true)) {
                $normalized['props'] = $this->validateCoreBlockProps($type, $props, $index);
            } else {
                $definition = $this->blockTypes->findDefinition($type);
                if ($definition !== null) {
                    $normalized['props'] = $this->validatePluginBlockProps($definition, $props, $index);
                }
            }

            $blocks[] = $normalized;
        }

        return ['blocks' => $blocks];
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function validateCoreBlockProps(string $type, array $props, int $index): array
    {
        if ($type === 'contact_form') {
            $formSlug = $props['form_slug'] ?? null;
            if (! is_string($formSlug) || $formSlug === '') {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.props.form_slug" => ['Contact form block requires a form_slug prop.'],
                ]);
            }
        }

        if (in_array($type, ['feature_grid', 'faq'], true)) {
            return $this->validateListBlockProps($type, $props, $index);
        }

        return $props;
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function validatePluginBlockProps(BlockTypeDefinition $definition, array $props, int $index): array
    {
        $normalized = $props;

        foreach ($definition->fields as $field) {
            if ($field->type === 'item_list') {
                continue;
            }

            $value = $props[$field->name] ?? null;

            if ($value !== null && ! is_string($value)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.props.{$field->name}" => ["{$field->label} must be a string."],
                ]);
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $props
     * @return array<string, mixed>
     */
    private function validateListBlockProps(string $type, array $props, int $index): array
    {
        $heading = $props['heading'] ?? '';
        if (! is_string($heading)) {
            throw ValidationException::withMessages([
                "content.blocks.{$index}.props.heading" => ['Heading must be a string.'],
            ]);
        }

        $items = $props['items'] ?? [];
        if (! is_array($items)) {
            throw ValidationException::withMessages([
                "content.blocks.{$index}.props.items" => ['Items must be an array.'],
            ]);
        }

        $normalizedItems = [];
        foreach ($items as $itemIndex => $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    "content.blocks.{$index}.props.items.{$itemIndex}" => ['Each item must be an object.'],
                ]);
            }

            if ($type === 'feature_grid') {
                $title = $item['title'] ?? '';
                $body = $item['body'] ?? '';
                if (! is_string($title) || ! is_string($body)) {
                    throw ValidationException::withMessages([
                        "content.blocks.{$index}.props.items.{$itemIndex}" => ['Feature items require string title and body.'],
                    ]);
                }

                $normalizedItems[] = [
                    'title' => $title,
                    'body' => $body,
                ];
            }

            if ($type === 'faq') {
                $question = $item['question'] ?? '';
                $answer = $item['answer'] ?? '';
                if (! is_string($question) || ! is_string($answer)) {
                    throw ValidationException::withMessages([
                        "content.blocks.{$index}.props.items.{$itemIndex}" => ['FAQ items require string question and answer.'],
                    ]);
                }

                $normalizedItems[] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
            }
        }

        $props['heading'] = $heading;
        $props['items'] = $normalizedItems;

        return $props;
    }
}
