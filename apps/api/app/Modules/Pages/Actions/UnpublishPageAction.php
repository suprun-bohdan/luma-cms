<?php

declare(strict_types=1);

namespace App\Modules\Pages\Actions;

use App\Models\User;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;

final class UnpublishPageAction
{
    public function execute(Page $page, User $user): Page
    {
        $page->status = PageStatus::Draft;
        $page->published_at = null;
        $page->updated_by = $user->id;
        $page->save();

        return $page->refresh();
    }
}
