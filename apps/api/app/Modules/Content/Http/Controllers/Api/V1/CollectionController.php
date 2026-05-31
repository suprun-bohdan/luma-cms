<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Content\Actions\CreateCollectionAction;
use App\Modules\Content\Actions\DeleteCollectionAction;
use App\Modules\Content\Actions\UpdateCollectionAction;
use App\Modules\Content\Http\Requests\StoreCollectionRequest;
use App\Modules\Content\Http\Requests\UpdateCollectionRequest;
use App\Modules\Content\Http\Resources\CollectionResource;
use App\Modules\Content\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CollectionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Collection::class);

        $collections = Collection::query()
            ->orderBy('name')
            ->get();

        return CollectionResource::collection($collections);
    }

    public function store(
        StoreCollectionRequest $request,
        CreateCollectionAction $action,
    ): JsonResponse {
        $this->authorize('create', Collection::class);

        $collection = $action->execute($request->validated());

        return (new CollectionResource($collection))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Collection $collection): CollectionResource
    {
        $this->authorize('view', $collection);

        return new CollectionResource($collection);
    }

    public function update(
        UpdateCollectionRequest $request,
        Collection $collection,
        UpdateCollectionAction $action,
    ): CollectionResource {
        $this->authorize('update', $collection);

        $collection = $action->execute($collection, $request->validated());

        return new CollectionResource($collection);
    }

    public function destroy(
        Collection $collection,
        DeleteCollectionAction $action,
    ): JsonResponse {
        $this->authorize('delete', $collection);

        $action->execute($collection);

        return response()->json(null, 204);
    }
}
