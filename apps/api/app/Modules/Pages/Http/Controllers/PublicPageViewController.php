<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageRenderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

final class PublicPageViewController extends Controller
{
    public function __construct(
        private readonly PageRenderService $pageRender,
    ) {
    }

    public function show(Page $page): View|Response
    {
        if ($page->status !== PageStatus::Published) {
            abort(404);
        }

        $html = $this->pageRender->renderHtml($page);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
