<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Media\Actions\DeleteMediaAction;
use App\Modules\Media\Actions\UpdateMediaAction;
use App\Modules\Media\Actions\UploadMediaAction;
use App\Modules\Media\Http\Requests\StoreMediaRequest;
use App\Modules\Media\Http\Requests\UpdateMediaRequest;
use App\Modules\Media\Http\Resources\MediaResource;
use App\Modules\Media\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MediaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Media::class);

        $media = Media::query()
            ->latest('created_at')
            ->get();

        return MediaResource::collection($media);
    }

    public function store(
        StoreMediaRequest $request,
        UploadMediaAction $action,
    ): JsonResponse {
        $this->authorize('create', Media::class);

        $media = $action->execute(
            $request->file('file'),
            $request->user(),
            $request->validated(),
        );

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Media $media): MediaResource
    {
        $this->authorize('view', $media);

        return new MediaResource($media);
    }

    public function update(
        UpdateMediaRequest $request,
        Media $media,
        UpdateMediaAction $action,
    ): MediaResource {
        $this->authorize('update', $media);

        $media = $action->execute($media, $request->validated());

        return new MediaResource($media);
    }

    public function destroy(
        Media $media,
        DeleteMediaAction $action,
    ): JsonResponse {
        $this->authorize('delete', $media);

        $action->execute($media);

        return response()->json(null, 204);
    }
}
