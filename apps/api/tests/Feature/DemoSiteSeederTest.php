<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Navigation\Models\Menu;
use App\Modules\Pages\Database\Seeders\DemoSiteSeeder;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class DemoSiteSeederTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_demo_site_seeder_creates_published_home_page_and_menus(): void
    {
        $this->seed(DemoSiteSeeder::class);

        $this->assertDatabaseHas('pages', [
            'slug' => 'home',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('menus', ['slug' => 'header']);
        $this->assertDatabaseHas('menus', ['slug' => 'footer']);

        $response = $this->get('/p/home');

        $response
            ->assertOk()
            ->assertSee('Build your business site with Luma')
            ->assertSee('Home')
            ->assertSee('Contact us');

        $this->assertDatabaseHas('forms', ['slug' => 'contact']);
    }

    public function test_public_footer_menu_api(): void
    {
        $this->seed(DemoSiteSeeder::class);

        $response = $this->getJson('/api/v1/public/menus/footer');

        $response
            ->assertOk()
            ->assertJsonPath('slug', 'footer')
            ->assertJsonPath('items.0.label', 'Luma CMS');
    }
}
