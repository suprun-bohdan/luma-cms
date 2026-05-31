<?php

declare(strict_types=1);

namespace Tests\Unit\Plugins;

use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Models\PluginCapability;
use App\Modules\Plugins\Services\CapabilityGate;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use App\Modules\Plugins\Services\PluginHookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PluginHookServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_after_page_published_dispatches_for_enabled_granted_plugin(): void
    {
        $plugin = Plugin::query()->create([
            'plugin_id' => 'luma.test-hooks',
            'name' => 'Test Hooks Plugin',
            'version' => '0.1.0',
            'status' => PluginStatus::Enabled,
            'manifest' => [
                'capabilities' => ['content.publish'],
                'extensionPoints' => ['content.afterPublish'],
            ],
            'path' => '/tmp/luma.test-hooks',
            'installed_at' => now(),
            'enabled_at' => now(),
        ]);

        PluginCapability::query()->create([
            'plugin_id' => $plugin->id,
            'capability' => 'content.publish',
            'granted' => true,
            'approved_at' => now(),
        ]);

        $dispatcher = app(ExtensionPointDispatcher::class);
        $called = false;
        $dispatcher->listen('luma.test-hooks', 'content.afterPublish', static function () use (&$called): void {
            $called = true;
        });

        $service = new PluginHookService($dispatcher, app(CapabilityGate::class));
        $page = Page::factory()->create(['slug' => 'hook-page']);
        $user = User::factory()->create();

        $service->afterPagePublished($page, $user);

        $this->assertTrue($called);
    }
}
