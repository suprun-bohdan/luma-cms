<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Services\AdminNavigationService;
use Illuminate\Http\JsonResponse;

final class AdminNavigationController extends Controller
{
    public function index(AdminNavigationService $navigation): JsonResponse
    {
        $this->authorize('viewAny', Page::class);

        return response()->json([
            'data' => $navigation->visibleItems(),
        ]);
    }
}
