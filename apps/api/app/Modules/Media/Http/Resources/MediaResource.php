<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Resources;

use App\Modules\Media\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Media\Models\Media */
final class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MediaStorageService $storage */
        $storage = app(MediaStorageService::class);

        $variants = collect($this->variants ?? [])
            ->map(function (mixed $variant) use ($storage): array {
                if (! is_array($variant)) {
                    return [];
                }

                $path = isset($variant['path']) && is_string($variant['path']) ? $variant['path'] : null;

                return [
                    'name' => $variant['name'] ?? null,
                    'path' => $path,
                    'url' => $path !== null
                        ? $storage->url($this->disk, $path)
                        : ($variant['url'] ?? null),
                    'width' => $variant['width'] ?? null,
                    'height' => $variant['height'] ?? null,
                ];
            })
            ->filter(fn (array $variant): bool => $variant !== [])
            ->values()
            ->all();

        return [
            'uuid' => $this->uuid,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'alt_text' => $this->alt_text,
            'url' => $storage->url($this->disk, $this->path),
            'variants' => $variants,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
