<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Content\Actions\CreateEntryAction;
use App\Modules\Content\Actions\DeleteEntryAction;
use App\Modules\Content\Actions\PublishEntryAction;
use App\Modules\Content\Actions\UnpublishEntryAction;
use App\Modules\Content\Actions\UpdateEntryAction;
use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Http\Requests\StoreEntryRequest;
use App\Modules\Content\Http\Requests\UpdateEntryRequest;
use App\Modules\Content\Http\Resources\EntryResource;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

final class EntryController extends Controller
{
    public function index(Request $request, Collection $collection): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Entry::class);

        $request->validate([
            'status' => ['sometimes', Rule::enum(EntryStatus::class)],
        ]);

        $query = $collection->entries()->latest('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        return EntryResource::collection($query->get());
    }

    public function store(
        StoreEntryRequest $request,
        Collection $collection,
        CreateEntryAction $action,
    ): JsonResponse {
        $this->authorize('create', Entry::class);

        $entry = $action->execute(
            $collection,
            $request->validated(),
            $request->user(),
        );

        return (new EntryResource($entry))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Entry $entry): EntryResource
    {
        $this->authorize('view', $entry);

        return new EntryResource($entry);
    }

    public function update(
        UpdateEntryRequest $request,
        Entry $entry,
        UpdateEntryAction $action,
    ): EntryResource {
        $this->authorize('update', $entry);

        $entry = $action->execute($entry, $request->validated(), $request->user());

        return new EntryResource($entry);
    }

    public function destroy(Entry $entry, DeleteEntryAction $action): JsonResponse
    {
        $this->authorize('delete', $entry);

        $action->execute($entry);

        return response()->json(null, 204);
    }

    public function publish(Entry $entry, PublishEntryAction $action, Request $request): EntryResource
    {
        $this->authorize('update', $entry);

        $entry = $action->execute($entry, $request->user());

        return new EntryResource($entry);
    }

    public function unpublish(Entry $entry, UnpublishEntryAction $action, Request $request): EntryResource
    {
        $this->authorize('update', $entry);

        $entry = $action->execute($entry, $request->user());

        return new EntryResource($entry);
    }
}
