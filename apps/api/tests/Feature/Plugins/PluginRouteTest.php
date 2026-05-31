<?php

declare(strict_types=1);

namespace Tests\Feature\Plugins;

use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\BlockTypeRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use App\Modules\Plugins\Services\PluginRouteRegistry;
use App\Modules\Plugins\Services\PluginRuntimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class PluginRouteTest extends TestCase
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
        app(PluginRuntimeService::class)->resetRuntimeState();

        $this->seedRbac();
    }

    public function test_approved_route_capability_allows_plugin_ping(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.test-routes'],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $this->postJson(
            '/api/v1/plugins/luma.test-routes/enable',
            [],
            $this->withBearer($this->adminUser()),
        )
            ->assertStatus(422)
            ->assertJsonPath('message', 'Dangerous capabilities require approval before enable.');

        $this->postJson(
            '/api/v1/plugins/luma.test-routes/capabilities/approve',
            ['capability' => 'routes.register'],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $this->postJson(
            '/api/v1/plugins/luma.test-routes/enable',
            [],
            $this->withBearer($this->adminUser()),
        )
            ->assertOk()
            ->assertJsonPath('status', PluginStatus::Enabled->value);

        $this->assertNotNull(
            app(PluginRouteRegistry::class)->match('luma.test-routes', 'GET', 'ping'),
            'Plugin route should be registered after enable.',
        );

        $response = $this->getJson(
            '/api/v1/plugins/luma.test-routes/ping',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('plugin_id', 'luma.test-routes');
    }
}
