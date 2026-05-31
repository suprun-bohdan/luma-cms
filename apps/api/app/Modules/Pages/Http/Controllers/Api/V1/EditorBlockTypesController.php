<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Services\BlockTypeService;
use Illuminate\Http\JsonResponse;

final class EditorBlockTypesController extends Controller
{
    public function index(BlockTypeService $blockTypes): JsonResponse
    {
        $this->authorize('viewAny', Page::class);

        return response()->json([
            'data' => $blockTypes->visibleDefinitions(),
        ]);
    }
}
