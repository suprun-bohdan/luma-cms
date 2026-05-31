<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Models\User;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class PagePreviewTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_unauthenticated_preview_is_rejected(): void
    {
        $page = Page::factory()->create(['slug' => 'draft-page']);

        $response = $this->getJson('/api/v1/pages/draft-page/preview-html');

        $response->assertUnauthorized();
    }

    public function test_admin_previews_draft_page_html(): void
    {
        $page = Page::factory()->create([
            'slug' => 'draft-page',
            'title' => 'Draft Title',
            'status' => PageStatus::Draft,
            'content' => [
                'blocks' => [
                    [
                        'id' => 'text-1',
                        'type' => 'rich_text',
                        'props' => ['body' => 'Preview body copy'],
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(
            '/api/v1/pages/draft-page/preview-html',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonStructure(['html'])
            ->assertSee('Preview body copy', false);
    }

    public function test_admin_previews_unsaved_content_via_post(): void
    {
        Page::factory()->create([
            'slug' => 'editable',
            'title' => 'Old Title',
            'status' => PageStatus::Draft,
            'content' => [
                'blocks' => [
                    [
                        'id' => 'text-1',
                        'type' => 'rich_text',
                        'props' => ['body' => 'Old body'],
                    ],
                ],
            ],
        ]);

        $response = $this->postJson(
            '/api/v1/pages/editable/preview-html',
            [
                'title' => 'New Title',
                'content' => [
                    'blocks' => [
                        [
                            'id' => 'text-1',
                            'type' => 'rich_text',
                            'props' => ['body' => 'Updated preview body'],
                        ],
                    ],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertSee('Updated preview body', false)
            ->assertSee('New Title', false)
            ->assertDontSee('Old body', false);
    }

    public function test_admin_previews_page_with_contact_form_block(): void
    {
        $this->seed(\App\Modules\Forms\Database\Seeders\ContactFormSeeder::class);

        Page::factory()->create([
            'slug' => 'contact-preview',
            'status' => PageStatus::Draft,
            'content' => [
                'blocks' => [
                    [
                        'id' => 'contact-1',
                        'type' => 'contact_form',
                        'props' => [
                            'form_slug' => 'contact',
                            'title' => 'Contact us',
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(
            '/api/v1/pages/contact-preview/preview-html',
            $this->withBearer($this->adminUser()),
        );

        $response->assertOk();

        $html = (string) $response->json('html');
        $this->assertStringContainsString('Contact us', $html);
        $this->assertStringContainsString('name="email"', $html);
    }

    public function test_user_without_pages_view_cannot_preview(): void
    {
        Page::factory()->create(['slug' => 'secret']);

        $user = User::factory()->create();

        $response = $this->getJson(
            '/api/v1/pages/secret/preview-html',
            $this->withBearer($user),
        );

        $response->assertForbidden();
    }

    public function test_admin_previews_unsaved_page_without_database_record(): void
    {
        $this->assertDatabaseMissing('pages', ['slug' => 'preview-only']);

        $response = $this->postJson(
            '/api/v1/pages/preview-html',
            [
                'slug' => 'preview-only',
                'title' => 'Preview Only',
                'content' => [
                    'blocks' => [
                        [
                            'id' => 'text-1',
                            'type' => 'rich_text',
                            'props' => ['body' => 'Ephemeral preview body'],
                        ],
                    ],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertSee('Ephemeral preview body', false)
            ->assertSee('Preview Only', false);

        $this->assertDatabaseMissing('pages', ['slug' => 'preview-only']);
    }
}
