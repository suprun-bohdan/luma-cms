<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Content\Models\Field */
final class FieldResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection_id' => $this->collection_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'config' => $this->config,
            'sort_order' => $this->sort_order,
            'required' => $this->required,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
