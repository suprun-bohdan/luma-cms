<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\Field;

final class DeleteFieldAction
{
    public function __construct(
        private readonly BumpCollectionSchemaVersionAction $bumpSchemaVersion,
    ) {
    }

    public function execute(Field $field): void
    {
        $collection = $field->collection;

        $field->delete();

        $this->bumpSchemaVersion->execute($collection);
    }
}
