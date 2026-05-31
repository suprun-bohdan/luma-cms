<?php

declare(strict_types=1);

namespace App\Modules\Pages\Actions;

use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageContentValidator;

final class UpdatePageAction
{
    public function __construct(
        private readonly PageContentValidator $contentValidator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Page $page, array $data, User $user): Page
    {
        if (array_key_exists('title', $data)) {
            $page->title = $data['title'];
        }

        if (array_key_exists('slug', $data)) {
            $page->slug = $data['slug'];
        }

        if (array_key_exists('template', $data)) {
            $page->template = $data['template'];
        }

        if (array_key_exists('content', $data)) {
            $page->content = is_array($data['content'])
                ? $this->contentValidator->validate($data['content'])
                : ['blocks' => []];
        }

        if (array_key_exists('seo', $data)) {
            $page->seo = $data['seo'];
        }

        $page->updated_by = $user->id;
        $page->save();

        return $page->refresh();
    }
}
