<?php

declare(strict_types=1);

namespace Tests\Feature\Plugins;

use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Plugins\Services\AdminNavigationRegistry;
use App\Modules\Plugins\Services\ExtensionPointDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class ContentHookTest extends TestCase
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

        $this->seedRbac();
    }

    public function test_page_publish_triggers_enabled_plugin_hook(): void
    {
        $this->postJson(
            '/api/v1/plugins/install',
            ['plugin_id' => 'luma.test-hooks'],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $this->postJson(
            '/api/v1/plugins/luma.test-hooks/enable',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $page = Page::factory()->create([
            'slug' => 'hook-target',
            'status' => PageStatus::Draft,
        ]);

        $this->postJson(
            '/api/v1/pages/hook-target/publish',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $this->assertSame(
            [
                'entity' => 'page',
                'slug' => 'hook-target',
                'plugin_id' => 'luma.test-hooks',
            ],
            Cache::get('luma.test-hooks.last_publish'),
        );
    }
}
