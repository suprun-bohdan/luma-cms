<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Models\User;
use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use App\Modules\Content\Services\EntryDataValidator;

final class CreateEntryAction
{
    public function __construct(
        private readonly EntryDataValidator $validator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Collection $collection, array $data, User $user): Entry
    {
        $validatedData = $this->validator->validate($collection, $data['data']);

        return Entry::query()->create([
            'collection_id' => $collection->id,
            'status' => EntryStatus::Draft,
            'data' => $validatedData,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
}
