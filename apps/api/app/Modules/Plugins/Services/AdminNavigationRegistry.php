<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

final class AdminNavigationRegistry
{
    /** @var array<int, array{plugin_id: string, label: string, to: string, sort_order: int}> */
    private array $items = [];

    public function register(string $pluginId, string $label, string $to, int $sortOrder = 100): void
    {
        $this->items[] = [
            'plugin_id' => $pluginId,
            'label' => $label,
            'to' => $to,
            'sort_order' => $sortOrder,
        ];
    }

    /**
     * @return list<array{plugin_id: string, label: string, to: string, sort_order: int}>
     */
    public function all(): array
    {
        $items = $this->items;

        usort(
            $items,
            static fn (array $left, array $right): int => [$left['sort_order'], $left['label']]
                <=> [$right['sort_order'], $right['label']],
        );

        return $items;
    }

    public function forgetPlugin(string $pluginId): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            static fn (array $item): bool => $item['plugin_id'] !== $pluginId,
        ));
    }

    public function clear(): void
    {
        $this->items = [];
    }
}
