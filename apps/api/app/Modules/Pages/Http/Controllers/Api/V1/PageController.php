<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Actions\CreatePageAction;
use App\Modules\Pages\Actions\DeletePageAction;
use App\Modules\Pages\Actions\PublishPageAction;
use App\Modules\Pages\Actions\UnpublishPageAction;
use App\Modules\Pages\Actions\UpdatePageAction;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Http\Requests\StorePageRequest;
use App\Modules\Pages\Http\Requests\UpdatePageRequest;
use App\Modules\Pages\Http\Resources\PageResource;
use App\Modules\Pages\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

final class PageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Page::class);

        $request->validate([
            'status' => ['sometimes', Rule::enum(PageStatus::class)],
        ]);

        $query = Page::query()->latest('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        return PageResource::collection($query->get());
    }

    public function store(
        StorePageRequest $request,
        CreatePageAction $action,
    ): JsonResponse {
        $this->authorize('create', Page::class);

        $page = $action->execute($request->validated(), $request->user());

        return (new PageResource($page))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Page $page): PageResource
    {
        $this->authorize('view', $page);

        return new PageResource($page);
    }

    public function update(
        UpdatePageRequest $request,
        Page $page,
        UpdatePageAction $action,
    ): PageResource {
        $this->authorize('update', $page);

        $page = $action->execute($page, $request->validated(), $request->user());

        return new PageResource($page);
    }

    public function destroy(Page $page, DeletePageAction $action): JsonResponse
    {
        $this->authorize('delete', $page);

        $action->execute($page);

        return response()->json(null, 204);
    }

    public function publish(
        Page $page,
        PublishPageAction $action,
        Request $request,
    ): PageResource {
        $this->authorize('publish', $page);

        $page = $action->execute($page, $request->user());

        return new PageResource($page);
    }

    public function unpublish(
        Page $page,
        UnpublishPageAction $action,
        Request $request,
    ): PageResource {
        $this->authorize('publish', $page);

        $page = $action->execute($page, $request->user());

        return new PageResource($page);
    }
}
