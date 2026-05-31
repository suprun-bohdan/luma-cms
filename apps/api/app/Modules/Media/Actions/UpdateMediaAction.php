<?php

declare(strict_types=1);

namespace App\Modules\Media\Actions;

use App\Modules\Media\Models\Media;

final class UpdateMediaAction
{
    /**
     * @param  array{alt_text?: string|null}  $data
     */
    public function execute(Media $media, array $data): Media
    {
        if (array_key_exists('alt_text', $data)) {
            $media->alt_text = $data['alt_text'];
        }

        $media->save();

        return $media->refresh();
    }
}
