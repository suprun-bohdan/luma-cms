<?php

declare(strict_types=1);

namespace Tests\Unit\Plugins;

use App\Modules\Plugins\Services\BlockTypeRegistry;
use App\Modules\Plugins\Support\BlockTypeDefinition;
use Tests\TestCase;

final class BlockTypeRegistryTest extends TestCase
{
    public function test_register_forget_and_find_plugin_block(): void
    {
        $registry = new BlockTypeRegistry();

        $registry->register(
            new BlockTypeDefinition(
                type: 'luma.test/banner',
                label: 'Banner',
                description: 'Test banner',
                category: 'content',
                defaultProps: ['text' => 'Hello'],
                fields: [],
                pluginId: 'luma.test',
                source: 'plugin',
            ),
            static fn (array $props): string => '<div>'.($props['text'] ?? '').'</div>',
        );

        $this->assertNotNull($registry->find('luma.test/banner'));
        $this->assertCount(1, $registry->definitions());

        $registry->forgetPlugin('luma.test');

        $this->assertNull($registry->find('luma.test/banner'));
        $this->assertCount(0, $registry->definitions());
    }
}
