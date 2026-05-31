<?php

declare(strict_types=1);

namespace App\Modules\Media\Actions;

use App\Modules\Media\Models\Media;
use App\Modules\Media\Services\MediaStoragePaths;
use App\Modules\Media\Services\MediaStorageService;

final class DeleteMediaAction
{
    public function __construct(
        private readonly MediaStorageService $storage,
    ) {
    }

    public function execute(Media $media): void
    {
        $variantPaths = collect($media->variants ?? [])
            ->pluck('path')
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all();

        $this->storage->deleteFiles(new MediaStoragePaths(
            disk: $media->disk,
            path: $media->path,
            variantPaths: $variantPaths,
        ));

        $media->delete();
    }
}
