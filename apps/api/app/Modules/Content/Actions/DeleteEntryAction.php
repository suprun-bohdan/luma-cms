<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Entry;

final class DeleteEntryAction
{
    public function execute(Entry $entry): void
    {
        $entry->delete();
    }
}
