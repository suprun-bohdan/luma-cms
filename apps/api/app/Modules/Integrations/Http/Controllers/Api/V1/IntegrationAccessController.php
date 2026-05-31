<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Content\Http\Controllers\Api\V1\PublicEntryController;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use App\Modules\Forms\Http\Resources\FormSubmissionResource;
use App\Modules\Forms\Models\Form;
use App\Modules\Media\Http\Controllers\Api\V1\PublicMediaController;
use App\Modules\Media\Models\Media;
use App\Modules\Pages\Http\Controllers\Api\V1\PublicPageController;
use App\Modules\Pages\Models\Page;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class IntegrationAccessController extends Controller
{
    public function __construct(
        private readonly PublicPageController $pages,
        private readonly PublicEntryController $entries,
        private readonly PublicMediaController $media,
    ) {
    }

    public function showPage(Page $page)
    {
        return $this->pages->show($page);
    }

    public function listEntries(Collection $collection)
    {
        return $this->entries->index($collection);
    }

    public function showEntry(Entry $entry)
    {
        return $this->entries->show($entry);
    }

    public function showMedia(Media $media)
    {
        return $this->media->show($media);
    }

    public function formSubmissions(Form $form): AnonymousResourceCollection
    {
        $submissions = $form->submissions()->orderByDesc('created_at')->paginate(50);

        return FormSubmissionResource::collection($submissions);
    }
}
