<?php

declare(strict_types=1);

namespace App\Modules\Pages\Actions;

use App\Models\User;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Services\PluginHookService;
use App\Modules\Integrations\Services\IntegrationEventEmitter;

final class PublishPageAction
{
    public function __construct(
        private readonly PluginHookService $pluginHooks,
        private readonly IntegrationEventEmitter $integrationEvents,
    ) {
    }

    public function execute(Page $page, User $user): Page
    {
        $page->status = PageStatus::Published;
        $page->published_at = now();
        $page->updated_by = $user->id;
        $page->save();

        $page = $page->refresh();
        $this->pluginHooks->afterPagePublished($page, $user);
        $this->integrationEvents->pagePublished($page);

        return $page;
    }
}
