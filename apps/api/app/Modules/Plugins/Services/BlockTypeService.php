<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Pages\Support\CoreBlockDefinitions;
use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Support\BlockTypeDefinition;

final class BlockTypeService
{
    public function __construct(
        private readonly BlockTypeRegistry $registry,
        private readonly CapabilityGate $capabilityGate,
    ) {
    }

    /**
     * @return list<string>
     */
    public function allowedTypeIds(): array
    {
        return array_map(
            static fn (BlockTypeDefinition $definition): string => $definition->type,
            $this->visibleDefinitionObjects(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function visibleDefinitions(): array
    {
        return array_map(
            static fn (BlockTypeDefinition $definition): array => $definition->toArray(),
            $this->visibleDefinitionObjects(),
        );
    }

    /**
     * @return list<BlockTypeDefinition>
     */
    public function visibleDefinitionObjects(): array
    {
        $definitions = CoreBlockDefinitions::all();

        foreach ($this->visiblePluginDefinitions() as $definition) {
            $definitions[] = $definition;
        }

        return $definitions;
    }

    public function findDefinition(string $type): ?BlockTypeDefinition
    {
        foreach ($this->visibleDefinitionObjects() as $definition) {
            if ($definition->type === $type) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @return list<BlockTypeDefinition>
     */
    private function visiblePluginDefinitions(): array
    {
        $enabledPluginIds = Plugin::query()
            ->where('status', PluginStatus::Enabled)
            ->with('capabilities')
            ->get()
            ->filter(fn (Plugin $plugin): bool => $this->capabilityGate->allows($plugin, 'editor.extend'))
            ->pluck('plugin_id')
            ->all();

        return array_values(array_filter(
            $this->registry->definitions(),
            static fn (BlockTypeDefinition $definition): bool => in_array($definition->pluginId, $enabledPluginIds, true),
        ));
    }
}
