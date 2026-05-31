<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Collection;
use Illuminate\Support\Str;

final class CreateCollectionAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Collection
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        return Collection::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'config' => $data['config'] ?? null,
            'schema_version' => $data['schema_version'] ?? 1,
        ]);
    }
}
