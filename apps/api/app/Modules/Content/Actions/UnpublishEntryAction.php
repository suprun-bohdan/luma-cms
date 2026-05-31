<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Models\User;
use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Models\Entry;

final class UnpublishEntryAction
{
    public function execute(Entry $entry, User $user): Entry
    {
        $entry->status = EntryStatus::Draft;
        $entry->published_at = null;
        $entry->updated_by = $user->id;
        $entry->save();

        return $entry->refresh();
    }
}
