<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Support\BlockTypeDefinition;
use InvalidArgumentException;

final class BlockTypeRegistry
{
    /** @var array<string, array{definition: BlockTypeDefinition, renderer: callable}> */
    private array $entries = [];

    public function register(BlockTypeDefinition $definition, callable $renderer): void
    {
        if ($definition->pluginId === null) {
            throw new InvalidArgumentException('Plugin block definitions require a plugin id.');
        }

        $this->entries[$definition->type] = [
            'definition' => $definition,
            'renderer' => $renderer,
        ];
    }

    /**
     * @return list<BlockTypeDefinition>
     */
    public function definitions(): array
    {
        return array_values(array_map(
            static fn (array $entry): BlockTypeDefinition => $entry['definition'],
            $this->entries,
        ));
    }

    /**
     * @return array{definition: BlockTypeDefinition, renderer: callable}|null
     */
    public function find(string $type): ?array
    {
        return $this->entries[$type] ?? null;
    }

    public function forgetPlugin(string $pluginId): void
    {
        foreach ($this->entries as $type => $entry) {
            if ($entry['definition']->pluginId === $pluginId) {
                unset($this->entries[$type]);
            }
        }
    }

    public function clear(): void
    {
        $this->entries = [];
    }
}
