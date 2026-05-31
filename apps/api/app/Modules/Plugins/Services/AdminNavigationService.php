<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use Illuminate\Support\Collection;

final class AdminNavigationService
{
    public function __construct(
        private readonly AdminNavigationRegistry $registry,
        private readonly CapabilityGate $capabilityGate,
    ) {
    }

    /**
     * @return list<array{plugin_id: string, label: string, to: string, sort_order: int}>
     */
    public function visibleItems(): array
    {
        $enabledPluginIds = Plugin::query()
            ->where('status', PluginStatus::Enabled)
            ->with('capabilities')
            ->get()
            ->filter(fn (Plugin $plugin): bool => $this->capabilityGate->allows($plugin, 'admin.extend'))
            ->pluck('plugin_id')
            ->all();

        return array_values(array_filter(
            $this->registry->all(),
            static fn (array $item): bool => in_array($item['plugin_id'], $enabledPluginIds, true),
        ));
    }
}
