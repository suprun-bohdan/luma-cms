<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Modules\Pages\Database\Seeders\DemoSiteSeeder;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class SitemapTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_sitemap_lists_published_pages(): void
    {
        $this->seed(DemoSiteSeeder::class);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(url('/p/home'), false)
            ->assertSee('<urlset', false);
    }

    public function test_sitemap_excludes_draft_pages(): void
    {
        Page::factory()->create(['slug' => 'draft-only', 'status' => PageStatus::Draft]);
        Page::factory()->published()->create(['slug' => 'published-page']);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertSee(url('/p/published-page'), false)
            ->assertDontSee(url('/p/draft-only'), false);
    }
}
