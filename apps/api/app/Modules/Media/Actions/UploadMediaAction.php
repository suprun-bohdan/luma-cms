<?php

declare(strict_types=1);

namespace App\Modules\Media\Actions;

use App\Models\User;
use App\Modules\Media\Jobs\GenerateMediaVariantsJob;
use App\Modules\Media\Models\Media;
use App\Modules\Media\Services\MediaStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class UploadMediaAction
{
    public function __construct(
        private readonly MediaStorageService $storage,
    ) {
    }

    /**
     * @param  array{alt_text?: string|null}  $data
     */
    public function execute(UploadedFile $file, User $user, array $data = []): Media
    {
        $uuid = (string) Str::uuid();
        $stored = $this->storage->store($file, $uuid);

        $media = Media::query()->create([
            'uuid' => $uuid,
            'filename' => $stored['filename'],
            'disk' => $stored['disk'],
            'path' => $stored['path'],
            'mime_type' => $stored['mime_type'],
            'size' => $stored['size'],
            'width' => $stored['width'],
            'height' => $stored['height'],
            'alt_text' => $data['alt_text'] ?? null,
            'uploaded_by' => $user->id,
        ]);

        if (str_starts_with($media->mime_type, 'image/')) {
            GenerateMediaVariantsJob::dispatchSync($media->id);
            $media->refresh();
        }

        return $media;
    }
}
