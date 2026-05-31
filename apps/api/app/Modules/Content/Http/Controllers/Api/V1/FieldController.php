<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Content\Actions\CreateFieldAction;
use App\Modules\Content\Actions\DeleteFieldAction;
use App\Modules\Content\Actions\UpdateFieldAction;
use App\Modules\Content\Http\Requests\StoreFieldRequest;
use App\Modules\Content\Http\Requests\UpdateFieldRequest;
use App\Modules\Content\Http\Resources\FieldResource;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Field;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FieldController extends Controller
{
    public function index(Collection $collection): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Field::class);

        $fields = $collection->fields()
            ->orderBy('sort_order')
            ->get();

        return FieldResource::collection($fields);
    }

    public function store(
        StoreFieldRequest $request,
        Collection $collection,
        CreateFieldAction $action,
    ): JsonResponse {
        $this->authorize('create', Field::class);

        $field = $action->execute($collection, $request->validated());

        return (new FieldResource($field))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Collection $collection, Field $field): FieldResource
    {
        $this->authorize('view', $field);

        return new FieldResource($field);
    }

    public function update(
        UpdateFieldRequest $request,
        Collection $collection,
        Field $field,
        UpdateFieldAction $action,
    ): FieldResource {
        $this->authorize('update', $field);

        $field = $action->execute($field, $request->validated());

        return new FieldResource($field);
    }

    public function destroy(
        Collection $collection,
        Field $field,
        DeleteFieldAction $action,
    ): JsonResponse {
        $this->authorize('delete', $field);

        $action->execute($field);

        return response()->json(null, 204);
    }
}
