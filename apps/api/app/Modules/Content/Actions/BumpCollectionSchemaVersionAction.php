<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Collection;

final class BumpCollectionSchemaVersionAction
{
    public function execute(Collection $collection): Collection
    {
        $collection->increment('schema_version');

        return $collection->refresh();
    }
}
