<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Actions;

use App\Modules\Navigation\Models\Menu;
use App\Modules\Navigation\Models\MenuItem;

final class SyncMenuItemsAction
{
    /**
     * @param  array<int, array<string, mixed>>|null  $items
     */
    public function execute(Menu $menu, ?array $items): void
    {
        if ($items === null) {
            return;
        }

        $menu->items()->delete();

        foreach ($items as $index => $item) {
            MenuItem::query()->create([
                'menu_id' => $menu->id,
                'label' => $item['label'],
                'page_slug' => $item['page_slug'] ?? null,
                'url' => $item['url'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);
        }
    }
}
