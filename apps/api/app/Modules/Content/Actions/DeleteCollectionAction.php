<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Collection;

final class DeleteCollectionAction
{
    public function execute(Collection $collection): void
    {
        $collection->delete();
    }
}
