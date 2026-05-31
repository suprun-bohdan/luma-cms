<?php

declare(strict_types=1);

namespace App\Modules\Pages\Actions;

use App\Models\User;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageContentValidator;

final class CreatePageAction
{
    public function __construct(
        private readonly PageContentValidator $contentValidator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, User $user): Page
    {
        $content = isset($data['content']) && is_array($data['content'])
            ? $this->contentValidator->validate($data['content'])
            : ['blocks' => []];

        return Page::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'status' => PageStatus::Draft,
            'template' => $data['template'] ?? 'default-page',
            'content' => $content,
            'seo' => $data['seo'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
