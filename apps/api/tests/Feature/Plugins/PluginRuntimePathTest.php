<?php

declare(strict_types=1);

namespace Tests\Feature\Plugins;

use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\BlockTypeRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use App\Modules\Plugins\Services\PluginRouteRegistry;
use App\Modules\Plugins\Services\PluginRuntimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PluginRuntimePathTest extends TestCase
{
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
    }

    public function test_plugin_entrypoint_outside_directory_is_rejected(): void
    {
        Plugin::query()->create([
            'plugin_id' => 'luma.test-path-escape',
            'name' => 'Test Path Escape Plugin',
            'version' => '0.1.0',
            'status' => PluginStatus::Enabled,
            'manifest' => json_decode(
                (string) file_get_contents(base_path('tests/fixtures/plugins/luma.test-path-escape/luma.plugin.json')),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'path' => 'luma.test-path-escape',
            'installed_at' => now(),
            'enabled_at' => now(),
        ]);

        app(PluginRuntimeService::class)->bootEnabledPlugins();

        $this->getJson('/api/v1/health')->assertOk();

        $this->assertDatabaseHas('plugins', [
            'plugin_id' => 'luma.test-path-escape',
            'status' => PluginStatus::Failed->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'plugin.boot_failed',
            'subject_type' => 'plugin',
            'subject_id' => 'luma.test-path-escape',
        ]);
    }
}
