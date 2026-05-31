<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Navigation\Http\Resources\MenuResource;
use App\Modules\Navigation\Models\Menu;

final class PublicMenuController extends Controller
{
    public function show(Menu $menu): MenuResource
    {
        return new MenuResource($menu->load('items'));
    }
}
