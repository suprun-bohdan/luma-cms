<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Field;

final class UpdateFieldAction
{
    public function __construct(
        private readonly BumpCollectionSchemaVersionAction $bumpSchemaVersion,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Field $field, array $data): Field
    {
        $schemaAffectingKeys = ['type', 'slug', 'config', 'required'];
        $shouldBump = false;

        foreach ($schemaAffectingKeys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $current = $field->getAttribute($key);
            $incoming = $data[$key];

            if ($key === 'config') {
                if ($current !== $incoming) {
                    $shouldBump = true;
                }

                continue;
            }

            if ($current != $incoming) {
                $shouldBump = true;
            }
        }

        $field->fill([
            'name' => $data['name'] ?? $field->name,
            'slug' => $data['slug'] ?? $field->slug,
            'type' => $data['type'] ?? $field->type,
            'config' => array_key_exists('config', $data) ? $data['config'] : $field->config,
            'sort_order' => $data['sort_order'] ?? $field->sort_order,
            'required' => $data['required'] ?? $field->required,
        ]);

        $field->save();

        if ($shouldBump) {
            $this->bumpSchemaVersion->execute($field->collection);
        }

        return $field->refresh();
    }
}
