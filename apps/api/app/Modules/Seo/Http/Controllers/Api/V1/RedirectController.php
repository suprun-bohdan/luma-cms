<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Seo\Actions\CreateRedirectAction;
use App\Modules\Seo\Actions\DeleteRedirectAction;
use App\Modules\Seo\Actions\UpdateRedirectAction;
use App\Modules\Seo\Http\Requests\StoreRedirectRequest;
use App\Modules\Seo\Http\Requests\UpdateRedirectRequest;
use App\Modules\Seo\Http\Resources\RedirectResource;
use App\Modules\Seo\Models\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class RedirectController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Redirect::class);

        $redirects = Redirect::query()->orderBy('from_path')->get();

        return RedirectResource::collection($redirects);
    }

    public function store(
        StoreRedirectRequest $request,
        CreateRedirectAction $action,
    ): JsonResponse {
        $this->authorize('create', Redirect::class);

        $redirect = $action->execute($request->validated());

        return (new RedirectResource($redirect))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Redirect $redirect): RedirectResource
    {
        $this->authorize('view', $redirect);

        return new RedirectResource($redirect);
    }

    public function update(
        UpdateRedirectRequest $request,
        Redirect $redirect,
        UpdateRedirectAction $action,
    ): RedirectResource {
        $this->authorize('update', $redirect);

        return new RedirectResource($action->execute($redirect, $request->validated()));
    }

    public function destroy(Redirect $redirect, DeleteRedirectAction $action): JsonResponse
    {
        $this->authorize('delete', $redirect);

        $action->execute($redirect);

        return response()->json(null, 204);
    }
}
