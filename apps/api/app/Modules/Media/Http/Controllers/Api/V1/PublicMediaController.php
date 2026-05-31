<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Media\Http\Resources\MediaResource;
use App\Modules\Media\Models\Media;

final class PublicMediaController extends Controller
{
    public function show(Media $media): MediaResource
    {
        return new MediaResource($media);
    }
}
