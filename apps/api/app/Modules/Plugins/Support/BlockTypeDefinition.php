<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Support;

final readonly class BlockTypeDefinition
{
    /**
     * @param  array<string, mixed>  $defaultProps
     * @param  list<BlockFieldDefinition>  $fields
     */
    public function __construct(
        public string $type,
        public string $label,
        public string $description,
        public string $category,
        public array $defaultProps,
        public array $fields,
        public ?string $pluginId = null,
        public string $source = 'core',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
            'label' => $this->label,
            'description' => $this->description,
            'category' => $this->category,
            'default_props' => $this->defaultProps,
            'fields' => array_map(
                static fn (BlockFieldDefinition $field): array => $field->toArray(),
                $this->fields,
            ),
            'source' => $this->source,
        ];

        if ($this->pluginId !== null) {
            $data['plugin_id'] = $this->pluginId;
        }

        return $data;
    }
}
