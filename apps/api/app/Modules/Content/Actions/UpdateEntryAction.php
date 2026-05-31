<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Models\User;
use App\Modules\Content\Models\Entry;
use App\Modules\Content\Services\EntryDataValidator;
use App\Modules\Plugins\Services\PluginHookService;

final class UpdateEntryAction
{
    public function __construct(
        private readonly EntryDataValidator $validator,
        private readonly PluginHookService $pluginHooks,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Entry $entry, array $data, User $user): Entry
    {
        $entry->loadMissing('collection');

        if (array_key_exists('data', $data)) {
            $validatedData = $this->validator->validate(
                $entry->collection,
                $data['data'],
                enforceRequired: false,
            );

            $entry->data = $validatedData;
        }

        $entry->updated_by = $user->id;
        $entry->save();

        $this->pluginHooks->afterEntryUpdated($entry->refresh(), $user);

        return $entry->refresh();
    }
}
