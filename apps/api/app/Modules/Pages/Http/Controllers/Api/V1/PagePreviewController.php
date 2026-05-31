<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Http\Requests\PreviewDraftPageHtmlRequest;
use App\Modules\Pages\Http\Requests\PreviewPageHtmlRequest;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageContentValidator;
use App\Modules\Pages\Services\PageRenderService;
use Illuminate\Http\JsonResponse;

final class PagePreviewController extends Controller
{
    public function __construct(
        private readonly PageRenderService $pageRender,
        private readonly PageContentValidator $contentValidator,
    ) {
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        return response()->json([
            'html' => $this->pageRender->renderHtml($page),
        ]);
    }

    public function store(PreviewPageHtmlRequest $request, Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        $data = $request->validated();
        $content = isset($data['content']) ? $this->contentValidator->validate($data['content']) : null;
        $seo = $data['seo'] ?? null;
        $title = isset($data['title']) ? (string) $data['title'] : null;

        return response()->json([
            'html' => $this->pageRender->renderHtml($page, $content, $seo, $title),
        ]);
    }

    public function draft(PreviewDraftPageHtmlRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Page::class);

        $data = $request->validated();
        $content = isset($data['content'])
            ? $this->contentValidator->validate($data['content'])
            : ['blocks' => []];
        $seo = $data['seo'] ?? null;

        return response()->json([
            'html' => $this->pageRender->renderDraftHtml(
                (string) $data['slug'],
                (string) $data['title'],
                $content,
                $seo,
            ),
        ]);
    }
}
