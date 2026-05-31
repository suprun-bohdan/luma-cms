<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Navigation\Models\Menu;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicPageViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_renders_html(): void
    {
        $page = Page::factory()->published()->create([
            'slug' => 'welcome',
            'title' => 'Welcome',
            'content' => [
                'blocks' => [
                    [
                        'id' => 'b1',
                        'type' => 'rich_text',
                        'props' => ['body' => 'Hello public site'],
                    ],
                ],
            ],
        ]);

        $response = $this->get('/p/'.$page->slug);

        $response
            ->assertOk()
            ->assertSee('Welcome')
            ->assertSee('Hello public site');
    }

    public function test_draft_page_returns_not_found(): void
    {
        $page = Page::factory()->create([
            'slug' => 'secret',
            'status' => PageStatus::Draft,
        ]);

        $response = $this->get('/p/'.$page->slug);

        $response->assertNotFound();
    }

    public function test_published_page_renders_seo_meta_tags(): void
    {
        $page = Page::factory()->published()->create([
            'slug' => 'seo-page',
            'title' => 'Page Title',
            'seo' => [
                'title' => 'Custom SEO Title',
                'description' => 'Custom description for search engines.',
            ],
        ]);

        $response = $this->get('/p/'.$page->slug);

        $response
            ->assertOk()
            ->assertSee('Custom SEO Title', false)
            ->assertSee('meta name="description" content="Custom description for search engines."', false)
            ->assertSee('property="og:title" content="Custom SEO Title"', false);
    }

    public function test_published_page_renders_canonical_url(): void
    {
        $page = Page::factory()->published()->create([
            'slug' => 'canonical-page',
        ]);

        $response = $this->get('/p/'.$page->slug);

        $response
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/p/canonical-page').'"', false)
            ->assertSee('property="og:url" content="'.url('/p/canonical-page').'"', false);
    }

    public function test_published_page_renders_contact_form_block(): void
    {
        $this->seed(\App\Modules\Forms\Database\Seeders\ContactFormSeeder::class);

        $page = Page::factory()->published()->create([
            'slug' => 'contact-page',
            'content' => [
                'blocks' => [
                    [
                        'id' => 'contact-1',
                        'type' => 'contact_form',
                        'props' => [
                            'form_slug' => 'contact',
                            'title' => 'Get in touch',
                            'submit_label' => 'Send',
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->get('/p/'.$page->slug);

        $response
            ->assertOk()
            ->assertSee('Get in touch')
            ->assertSee('name="name"', false)
            ->assertSee('/public/forms/contact/submit', false);
    }
}
