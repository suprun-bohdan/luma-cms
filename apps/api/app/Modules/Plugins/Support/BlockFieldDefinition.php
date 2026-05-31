<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Support;

final readonly class BlockFieldDefinition
{
    /**
     * @param  list<BlockFieldDefinition>|null  $itemFields
     */
    public function __construct(
        public string $name,
        public string $label,
        public string $type,
        public ?string $placeholder = null,
        public ?array $itemFields = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public static function fromArray(array $field): self
    {
        $itemFields = null;

        if (isset($field['itemFields']) && is_array($field['itemFields'])) {
            $itemFields = array_values(array_map(
                static fn (mixed $item): self => self::fromArray(is_array($item) ? $item : []),
                $field['itemFields'],
            ));
        }

        return new self(
            name: is_string($field['name'] ?? null) ? $field['name'] : '',
            label: is_string($field['label'] ?? null) ? $field['label'] : '',
            type: is_string($field['type'] ?? null) ? $field['type'] : 'text',
            placeholder: is_string($field['placeholder'] ?? null) ? $field['placeholder'] : null,
            itemFields: $itemFields,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
        ];

        if ($this->placeholder !== null) {
            $data['placeholder'] = $this->placeholder;
        }

        if ($this->itemFields !== null) {
            $data['item_fields'] = array_map(
                static fn (self $field): array => $field->toArray(),
                $this->itemFields,
            );
        }

        return $data;
    }
}
