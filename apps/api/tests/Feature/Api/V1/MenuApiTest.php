<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Media\Models\Media;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class MenuApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_creates_menu_with_items(): void
    {
        Page::factory()->published()->create(['slug' => 'about']);

        $response = $this->postJson(
            '/api/v1/menus',
            [
                'name' => 'Header',
                'slug' => 'header',
                'items' => [
                    ['label' => 'About', 'page_slug' => 'about', 'sort_order' => 0],
                    ['label' => 'External', 'url' => 'https://example.com', 'sort_order' => 1],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('slug', 'header')
            ->assertJsonCount(2, 'items');

        $this->assertDatabaseHas('menu_items', [
            'label' => 'About',
            'page_slug' => 'about',
        ]);
    }

    public function test_public_menu_show(): void
    {
        $menu = Menu::factory()->create(['slug' => 'header']);
        $menu->items()->create([
            'label' => 'Home',
            'page_slug' => 'home',
            'sort_order' => 0,
        ]);

        $response = $this->getJson('/api/v1/public/menus/'.$menu->slug);

        $response
            ->assertOk()
            ->assertJsonPath('slug', 'header')
            ->assertJsonPath('items.0.resolved_url', '/p/home');
    }

    public function test_editor_cannot_delete_menu(): void
    {
        $menu = Menu::factory()->create();

        $response = $this->deleteJson(
            '/api/v1/menus/'.$menu->slug,
            [],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();
    }
}
