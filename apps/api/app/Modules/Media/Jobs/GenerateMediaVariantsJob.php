<?php

declare(strict_types=1);

namespace App\Modules\Media\Jobs;

use App\Modules\Media\Models\Media;
use App\Modules\Media\Services\MediaVariantService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class GenerateMediaVariantsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $mediaId,
    ) {
    }

    public function handle(MediaVariantService $variants): void
    {
        $media = Media::query()->find($this->mediaId);

        if ($media === null) {
            return;
        }

        $generated = $variants->generateThumbnail($media);

        if ($generated === []) {
            return;
        }

        $media->variants = $generated;
        $media->save();
    }
}
