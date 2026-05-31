<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Http\Resources\PageResource;
use App\Modules\Pages\Models\Page;

final class PublicPageController extends Controller
{
    public function show(Page $page): PageResource
    {
        if ($page->status !== PageStatus::Published) {
            abort(404);
        }

        return new PageResource($page);
    }
}
