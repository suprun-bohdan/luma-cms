<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Http\Resources\EntryResource;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicEntryController extends Controller
{
    public function index(Collection $collection): AnonymousResourceCollection
    {
        $entries = $collection->entries()
            ->where('status', EntryStatus::Published)
            ->latest('published_at')
            ->get();

        return EntryResource::collection($entries);
    }

    public function show(Entry $entry): EntryResource
    {
        if ($entry->status !== EntryStatus::Published) {
            abort(404);
        }

        return new EntryResource($entry);
    }
}
