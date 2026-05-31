<?php

declare(strict_types=1);

namespace App\Modules\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class MediaStorageService
{
    /**
     * @return array{
     *     disk: string,
     *     path: string,
     *     filename: string,
     *     mime_type: string,
     *     size: int,
     *     width: int|null,
     *     height: int|null
     * }
     */
    public function store(UploadedFile $file, string $uuid): array
    {
        $disk = (string) config('media.disk', 'public');
        $directory = trim((string) config('media.directory', 'media'), '/');
        $filename = $this->sanitizeFilename($file->getClientOriginalName());
        $path = $directory.'/'.$uuid.'/'.$filename;

        Storage::disk($disk)->putFileAs($directory.'/'.$uuid, $file, $filename);

        [$width, $height] = $this->resolveImageDimensions($file);

        return [
            'disk' => $disk,
            'path' => $path,
            'filename' => $filename,
            'mime_type' => (string) $file->getMimeType(),
            'size' => (int) $file->getSize(),
            'width' => $width,
            'height' => $height,
        ];
    }

    public function deleteFiles(MediaStoragePaths $paths): void
    {
        $disk = Storage::disk($paths->disk);

        if ($disk->exists($paths->path)) {
            $disk->delete($paths->path);
        }

        foreach ($paths->variantPaths as $variantPath) {
            if ($disk->exists($variantPath)) {
                $disk->delete($variantPath);
            }
        }

        $directory = dirname($paths->path);
        if ($directory !== '.' && $disk->exists($directory)) {
            $disk->deleteDirectory($directory);
        }
    }

    public function url(string $disk, string $path): string
    {
        return Storage::disk($disk)->url($path);
    }

    private function sanitizeFilename(string $filename): string
    {
        $basename = basename($filename);
        $sanitized = preg_replace('/[^A-Za-z0-9._-]+/', '-', $basename) ?? 'file';

        return trim($sanitized, '-_') !== '' ? trim($sanitized, '-_') : 'file';
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function resolveImageDimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return [null, null];
        }

        $size = @getimagesize($file->getRealPath() ?: '');

        if ($size === false) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }
}

final class MediaStoragePaths
{
    /**
     * @param  list<string>  $variantPaths
     */
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
        public readonly array $variantPaths = [],
    ) {
    }
}
