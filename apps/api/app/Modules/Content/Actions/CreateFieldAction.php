<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Field;
use Illuminate\Support\Str;

final class CreateFieldAction
{
    public function __construct(
        private readonly BumpCollectionSchemaVersionAction $bumpSchemaVersion,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Collection $collection, array $data): Field
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        $sortOrder = $data['sort_order'] ?? (
            (int) $collection->fields()->max('sort_order') + 1
        );

        $field = $collection->fields()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $data['type'],
            'config' => $data['config'] ?? null,
            'sort_order' => $sortOrder,
            'required' => $data['required'] ?? false,
        ]);

        $this->bumpSchemaVersion->execute($collection);

        return $field->refresh();
    }
}
