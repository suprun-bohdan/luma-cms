<?php

declare(strict_types=1);

namespace App\Modules\Pages\Actions;

use App\Modules\Pages\Models\Page;

final class DeletePageAction
{
    public function execute(Page $page): void
    {
        $page->delete();
    }
}
