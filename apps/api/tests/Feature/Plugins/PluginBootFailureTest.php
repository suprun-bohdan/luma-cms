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
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class PluginBootFailureTest extends TestCase
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

    public function test_failing_plugin_boot_is_contained_and_audited(): void
    {
        Plugin::query()->create([
            'plugin_id' => 'luma.test-boot-fail',
            'name' => 'Test Boot Fail Plugin',
            'version' => '0.1.0',
            'status' => PluginStatus::Enabled,
            'manifest' => json_decode(
                (string) file_get_contents(base_path('tests/fixtures/plugins/luma.test-boot-fail/luma.plugin.json')),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'path' => 'luma.test-boot-fail',
            'installed_at' => now(),
            'enabled_at' => now(),
        ]);

        app(PluginRuntimeService::class)->bootEnabledPlugins();

        $this->getJson('/api/v1/health')->assertOk();

        $this->assertDatabaseHas('plugins', [
            'plugin_id' => 'luma.test-boot-fail',
            'status' => PluginStatus::Failed->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'plugin.boot_failed',
            'subject_type' => 'plugin',
            'subject_id' => 'luma.test-boot-fail',
        ]);
    }
}
