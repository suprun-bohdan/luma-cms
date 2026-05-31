<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Navigation\Actions\SyncMenuItemsAction;
use App\Modules\Navigation\Http\Requests\StoreMenuRequest;
use App\Modules\Navigation\Http\Requests\UpdateMenuRequest;
use App\Modules\Navigation\Http\Resources\MenuResource;
use App\Modules\Navigation\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MenuController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Menu::class);

        $menus = Menu::query()->with('items')->orderBy('name')->get();

        return MenuResource::collection($menus);
    }

    public function store(
        StoreMenuRequest $request,
        SyncMenuItemsAction $syncItems,
    ): JsonResponse {
        $this->authorize('create', Menu::class);

        $data = $request->validated();
        $items = $data['items'] ?? null;
        unset($data['items']);

        $menu = Menu::query()->create($data);
        $syncItems->execute($menu, $items);

        return (new MenuResource($menu->load('items')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Menu $menu): MenuResource
    {
        $this->authorize('view', $menu);

        return new MenuResource($menu->load('items'));
    }

    public function update(
        UpdateMenuRequest $request,
        Menu $menu,
        SyncMenuItemsAction $syncItems,
    ): MenuResource {
        $this->authorize('update', $menu);

        $data = $request->validated();
        $items = $data['items'] ?? null;
        unset($data['items']);

        $menu->fill($data);
        $menu->save();
        $syncItems->execute($menu, $items);

        return new MenuResource($menu->load('items'));
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $this->authorize('delete', $menu);

        $menu->delete();

        return response()->json(null, 204);
    }
}
