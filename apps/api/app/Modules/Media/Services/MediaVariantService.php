<?php

declare(strict_types=1);

namespace App\Modules\Media\Services;

use App\Models\User;
use App\Modules\Media\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class MediaVariantService
{
    public function __construct(
        private readonly MediaStorageService $storage,
    ) {
    }

    /**
     * @return list<array{name: string, path: string, url: string, width: int|null, height: int|null}>
     */
    public function generateThumbnail(Media $media): array
    {
        if (! str_starts_with($media->mime_type, 'image/')) {
            return [];
        }

        if (! function_exists('imagecreatefromstring')) {
            return [];
        }

        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            return [];
        }

        $contents = $disk->get($media->path);
        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return [];
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetWidth = min((int) config('media.thumbnail_width', 400), $sourceWidth);

        if ($targetWidth <= 0 || $sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);

            return [];
        }

        $targetHeight = (int) round($sourceHeight * ($targetWidth / $sourceWidth));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($target === false) {
            imagedestroy($source);

            return [];
        }

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        ob_start();
        imagejpeg($target, null, 85);
        $binary = ob_get_clean() ?: '';

        imagedestroy($source);
        imagedestroy($target);

        if ($binary === '') {
            return [];
        }

        $variantPath = dirname($media->path).'/thumbnail.jpg';
        $disk->put($variantPath, $binary);

        return [[
            'name' => 'thumbnail',
            'path' => $variantPath,
            'url' => $this->storage->url($media->disk, $variantPath),
            'width' => $targetWidth,
            'height' => $targetHeight,
        ]];
    }
}
