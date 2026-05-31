<?php

declare(strict_types=1);

namespace Tests\Feature\Plugins;

use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class PluginLifecycleTest extends TestCase
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

    public function test_admin_discovers_demo_plugin(): void
    {
        $response = $this->getJson('/api/v1/plugins/discover', $this->withBearer($this->adminUser()));

        $response
            ->assertOk()
            ->assertJsonFragment([
                'plugin_id' => 'luma.demo',
                'installed' => false,
            ]);
    }

    public function test_admin_installs_and_enables_demo_plugin(): void
    {
        $install = $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.demo'],
            $this->withBearer($this->adminUser()),
        );

        $install
            ->assertCreated()
            ->assertJsonPath('plugin_id', 'luma.demo')
            ->assertJsonPath('status', PluginStatus::Installed->value);

        $enable = $this->postJson(
            '/api/v1/plugins/luma.demo/enable',
            [],
            $this->withBearer($this->adminUser()),
        );

        $enable
            ->assertOk()
            ->assertJsonPath('status', PluginStatus::Enabled->value);

        $this->assertDatabaseHas('plugins', [
            'plugin_id' => 'luma.demo',
            'status' => PluginStatus::Enabled->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'plugin.enabled',
            'subject_id' => 'luma.demo',
        ]);
    }

    public function test_editor_cannot_manage_plugins(): void
    {
        $response = $this->getJson('/api/v1/plugins', $this->withBearer($this->editorUser()));

        $response->assertForbidden();
    }

    public function test_admin_lists_audit_logs(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.demo'],
            $this->withBearer($this->adminUser()),
        );

        $response = $this->getJson('/api/v1/audit-logs', $this->withBearer($this->adminUser()));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.action', 'plugin.installed');
    }

    public function test_dangerous_capability_requires_approval_before_enable(): void
    {
        config([
            'plugins.path' => base_path('tests/fixtures/plugins'),
        ]);

        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.test-dangerous'],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $this->postJson(
            '/api/v1/plugins/luma.test-dangerous/enable',
            [],
            $this->withBearer($this->adminUser()),
        )
            ->assertStatus(422)
            ->assertJsonPath('message', 'Dangerous capabilities require approval before enable.');

        $this->postJson(
            '/api/v1/plugins/luma.test-dangerous/capabilities/approve',
            ['capability' => 'routes.register'],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $this->postJson(
            '/api/v1/plugins/luma.test-dangerous/enable',
            [],
            $this->withBearer($this->adminUser()),
        )
            ->assertOk()
            ->assertJsonPath('status', PluginStatus::Enabled->value);
    }

    public function test_admin_uninstalls_plugin(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.demo'],
            $this->withBearer($this->adminUser()),
        );

        $response = $this->deleteJson(
            '/api/v1/plugins/luma.demo',
            [],
            $this->withBearer($this->adminUser()),
        );

        $response->assertNoContent();
        $this->assertDatabaseMissing('plugins', ['plugin_id' => 'luma.demo']);
    }
}
