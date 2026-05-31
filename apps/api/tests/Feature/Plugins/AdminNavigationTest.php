<?php

declare(strict_types=1);

namespace Tests\Feature\Plugins;

use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class AdminNavigationTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'plugins.path' => dirname(__DIR__, 5).'/plugins',
        ]);

        app(ExtensionPointDispatcher::class)->clear();
        app(AdminNavigationRegistry::class)->clear();

        $this->seedRbac();
    }

    public function test_enabled_demo_plugin_exposes_navigation_item(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.demo'],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $this->postJson(
            '/api/v1/plugins/luma.demo/enable',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $response = $this->getJson(
            '/api/v1/admin/navigation-items',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonFragment([
                'plugin_id' => 'luma.demo',
                'label' => 'Demo insights',
                'to' => '/plugins',
            ]);
    }

    public function test_disabled_demo_plugin_hides_navigation_item(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.demo'],
            $this->withBearer($this->adminUser()),
        );

        $this->postJson(
            '/api/v1/plugins/luma.demo/enable',
            [],
            $this->withBearer($this->adminUser()),
        );

        $this->postJson(
            '/api/v1/plugins/luma.demo/disable',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $response = $this->getJson(
            '/api/v1/admin/navigation-items',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonMissing(['label' => 'Demo insights']);
    }
}
