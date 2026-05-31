<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Modules\Media\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
final class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $uuid = (string) Str::uuid();

        return [
            'uuid' => $uuid,
            'filename' => 'sample.jpg',
            'disk' => 'public',
            'path' => 'media/'.$uuid.'/sample.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'width' => 800,
            'height' => 600,
            'alt_text' => null,
            'variants' => [],
            'uploaded_by' => User::factory(),
        ];
    }
}
