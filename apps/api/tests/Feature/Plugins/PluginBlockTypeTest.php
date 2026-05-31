<?php

declare(strict_types=1);

namespace Tests\Feature\Plugins;

use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\BlockTypeRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use App\Modules\Plugins\Services\PluginRouteRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class PluginBlockTypeTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'plugins.path' => base_path('tests/fixtures/plugins'),
        ]);

        app(ExtensionPointDispatcher::class)->clear();
        app(AdminNavigationRegistry::class)->clear();
        app(BlockTypeRegistry::class)->clear();
        app(PluginRouteRegistry::class)->clear();

        $this->seedRbac();
    }

    public function test_enabled_plugin_exposes_block_type_and_renders_on_page(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.test-blocks'],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $this->postJson(
            '/api/v1/plugins/luma.test-blocks/enable',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $this->getJson(
            '/api/v1/editor/block-types',
            $this->withBearer($this->adminUser()),
        )
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'luma.test-blocks/banner',
                'source' => 'plugin',
            ]);

        $response = $this->postJson(
            '/api/v1/pages',
            [
                'title' => 'Plugin block page',
                'slug' => 'plugin-block-page',
                'template' => 'default-page',
                'content' => [
                    'blocks' => [
                        [
                            'id' => 'banner-1',
                            'type' => 'luma.test-blocks/banner',
                            'props' => ['text' => 'Rendered plugin banner'],
                        ],
                    ],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response->assertCreated();

        $page = Page::query()->where('slug', 'plugin-block-page')->firstOrFail();
        $page->update(['status' => PageStatus::Published, 'published_at' => now()]);

        $public = $this->get('/p/plugin-block-page');

        $public
            ->assertOk()
            ->assertSee('Rendered plugin banner', false);
    }
}
