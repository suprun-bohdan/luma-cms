<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class PageApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_unauthenticated_list_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/pages');

        $response->assertUnauthorized();
    }

    public function test_admin_creates_draft_page(): void
    {
        $response = $this->postJson(
            '/api/v1/pages',
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => [
                    'blocks' => [
                        [
                            'id' => 'block-1',
                            'type' => 'rich_text',
                            'props' => ['body' => 'Welcome'],
                        ],
                    ],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('slug', 'about-us')
            ->assertJsonPath('published_at', null);

        $this->assertDatabaseHas('pages', [
            'slug' => 'about-us',
            'status' => PageStatus::Draft->value,
        ]);
    }

    public function test_rejects_invalid_block_type(): void
    {
        $response = $this->postJson(
            '/api/v1/pages',
            [
                'title' => 'Bad Page',
                'slug' => 'bad-page',
                'content' => [
                    'blocks' => [
                        [
                            'id' => 'block-1',
                            'type' => 'unknown',
                            'props' => [],
                        ],
                    ],
                ],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_admin_updates_page(): void
    {
        $page = Page::factory()->create([
            'title' => 'Old Title',
            'slug' => 'old-title',
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->putJson(
            '/api/v1/pages/'.$page->slug,
            ['title' => 'New Title'],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('title', 'New Title');
    }

    public function test_publish_sets_published_status(): void
    {
        $page = Page::factory()->create([
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->postJson(
            '/api/v1/pages/'.$page->slug.'/publish',
            [],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('status', 'published');

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => PageStatus::Published->value,
        ]);
    }

    public function test_unpublish_returns_to_draft(): void
    {
        $page = Page::factory()->published()->create([
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->postJson(
            '/api/v1/pages/'.$page->slug.'/unpublish',
            [],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('published_at', null);
    }

    public function test_editor_cannot_delete_page(): void
    {
        $page = Page::factory()->create([
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->deleteJson(
            '/api/v1/pages/'.$page->slug,
            [],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function test_public_page_show_for_published(): void
    {
        $page = Page::factory()->published()->create([
            'slug' => 'public-page',
        ]);

        $response = $this->getJson('/api/v1/public/pages/'.$page->slug);

        $response
            ->assertOk()
            ->assertJsonPath('slug', 'public-page')
            ->assertJsonPath('status', 'published');
    }

    public function test_public_page_returns_404_for_draft(): void
    {
        $page = Page::factory()->create(['slug' => 'draft-page']);

        $response = $this->getJson('/api/v1/public/pages/'.$page->slug);

        $response->assertNotFound();
    }

    public function test_filters_pages_by_status(): void
    {
        Page::factory()->create([
            'status' => PageStatus::Draft,
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        Page::factory()->published()->create([
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->getJson(
            '/api/v1/pages?status=published',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'published');
    }
}
