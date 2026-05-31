<?php

declare(strict_types=1);

namespace App\Modules\Content\Actions;

use App\Models\User;
use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Models\Entry;
use App\Modules\Content\Models\EntryVersion;
use App\Modules\Content\Services\EntryDataValidator;
use App\Modules\Plugins\Services\PluginHookService;

final class PublishEntryAction
{
    public function __construct(
        private readonly EntryDataValidator $validator,
        private readonly PluginHookService $pluginHooks,
    ) {
    }

    public function execute(Entry $entry, User $user): Entry
    {
        $entry->loadMissing('collection');

        $this->validator->validate($entry->collection, $entry->data ?? [], enforceRequired: true);

        $entry->status = EntryStatus::Published;
        $entry->published_at = now();
        $entry->updated_by = $user->id;
        $entry->save();

        EntryVersion::query()->create([
            'entry_id' => $entry->id,
            'schema_version' => $entry->collection->schema_version,
            'data' => $entry->data,
            'created_by' => $user->id,
            'created_at' => now(),
        ]);

        $entry = $entry->refresh();
        $this->pluginHooks->afterEntryPublished($entry, $user);

        return $entry;
    }
}
