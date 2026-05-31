<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Collection;

final class UpdateCollectionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Collection $collection, array $data): Collection
    {
        $collection->fill([
            'name' => $data['name'] ?? $collection->name,
            'slug' => $data['slug'] ?? $collection->slug,
            'description' => array_key_exists('description', $data)
                ? $data['description']
                : $collection->description,
            'config' => array_key_exists('config', $data)
                ? $data['config']
                : $collection->config,
            'schema_version' => $data['schema_version'] ?? $collection->schema_version,
        ]);

        $collection->save();

        return $collection->refresh();
    }
}
