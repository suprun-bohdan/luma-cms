<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Models\User;
use App\Modules\Content\Models\Entry;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Events\ContentLifecycleEvent;
use App\Modules\Plugins\Models\Plugin;
use Illuminate\Support\Collection;

final class PluginHookService
{
    /** @var Collection<int, Plugin>|null */
    private ?Collection $enabledPlugins = null;

    /** @var array<string, string> */
    private const EXTENSION_POINT_CAPABILITIES = [
        'content.afterCreate' => 'content.create',
        'content.afterUpdate' => 'content.update',
        'content.afterPublish' => 'content.publish',
    ];

    public function __construct(
        private readonly ExtensionPointDispatcher $dispatcher,
        private readonly CapabilityGate $capabilityGate,
    ) {
    }

    public function afterEntryCreated(Entry $entry, User $user): void
    {
        $entry->loadMissing('collection');

        $this->dispatchContentHook(
            'content.afterCreate',
            new ContentLifecycleEvent(
                entity: 'entry',
                id: $entry->id,
                slug: (string) $entry->id,
                action: 'create',
                actorId: $user->id,
                meta: [
                    'collection_id' => $entry->collection_id,
                    'collection_slug' => $entry->collection->slug,
                ],
            ),
        );
    }

    public function afterEntryUpdated(Entry $entry, User $user): void
    {
        $entry->loadMissing('collection');

        $this->dispatchContentHook(
            'content.afterUpdate',
            new ContentLifecycleEvent(
                entity: 'entry',
                id: $entry->id,
                slug: (string) $entry->id,
                action: 'update',
                actorId: $user->id,
                meta: [
                    'collection_id' => $entry->collection_id,
                    'collection_slug' => $entry->collection->slug,
                ],
            ),
        );
    }

    public function afterEntryPublished(Entry $entry, User $user): void
    {
        $entry->loadMissing('collection');

        $this->dispatchContentHook(
            'content.afterPublish',
            new ContentLifecycleEvent(
                entity: 'entry',
                id: $entry->id,
                slug: (string) $entry->id,
                action: 'publish',
                actorId: $user->id,
                meta: [
                    'collection_id' => $entry->collection_id,
                    'collection_slug' => $entry->collection->slug,
                ],
            ),
        );
    }

    public function afterPagePublished(Page $page, User $user): void
    {
        $this->dispatchContentHook(
            'content.afterPublish',
            new ContentLifecycleEvent(
                entity: 'page',
                id: $page->id,
                slug: $page->slug,
                action: 'publish',
                actorId: $user->id,
                meta: [
                    'title' => $page->title,
                    'page_slug' => $page->slug,
                ],
            ),
        );
    }

    private function dispatchContentHook(string $extensionPoint, ContentLifecycleEvent $event): void
    {
        $requiredCapability = self::EXTENSION_POINT_CAPABILITIES[$extensionPoint] ?? null;

        if ($requiredCapability === null) {
            return;
        }

        $this->dispatcher->dispatch(
            $extensionPoint,
            $event,
            fn (string $pluginId): bool => $this->pluginCanHandleHook($pluginId, $extensionPoint, $requiredCapability),
        );
    }

    private function pluginCanHandleHook(string $pluginId, string $extensionPoint, string $requiredCapability): bool
    {
        $plugin = $this->enabledPlugins()->firstWhere('plugin_id', $pluginId);

        if ($plugin === null) {
            return false;
        }

        $declaredPoints = $plugin->manifest['extensionPoints'] ?? [];

        if (! is_array($declaredPoints) || ! in_array($extensionPoint, $declaredPoints, true)) {
            return false;
        }

        return $this->capabilityGate->allows($plugin, $requiredCapability);
    }

    /**
     * @return Collection<int, Plugin>
     */
    private function enabledPlugins(): Collection
    {
        if ($this->enabledPlugins === null) {
            $this->enabledPlugins = Plugin::query()
                ->where('status', PluginStatus::Enabled)
                ->with('capabilities')
                ->orderBy('plugin_id')
                ->get();
        }

        return $this->enabledPlugins;
    }
}
