<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Content\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class CollectionApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/v1/collections');

        $response->assertUnauthorized();
    }

    public function test_lists_collections(): void
    {
        Collection::factory()->count(2)->create();

        $response = $this->getJson(
            '/api/v1/collections',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'config',
                        'schema_version',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_creates_collection(): void
    {
        $response = $this->postJson(
            '/api/v1/collections',
            [
                'name' => 'Blog Posts',
                'description' => 'Articles and news',
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Blog Posts')
            ->assertJsonPath('data.slug', 'blog-posts')
            ->assertJsonPath('data.schema_version', 1);

        $this->assertDatabaseHas('collections', [
            'name' => 'Blog Posts',
            'slug' => 'blog-posts',
        ]);
    }

    public function test_shows_collection_by_slug(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Pages',
            'slug' => 'pages',
        ]);

        $response = $this->getJson(
            '/api/v1/collections/'.$collection->slug,
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.slug', 'pages');
    }

    public function test_updates_collection(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $response = $this->putJson(
            '/api/v1/collections/'.$collection->slug,
            [
                'name' => 'New Name',
                'slug' => 'new-name',
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.slug', 'new-name');
    }

    public function test_deletes_collection_as_admin(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(
            '/api/v1/collections/'.$collection->slug,
            [],
            $this->withBearer($this->adminUser()),
        );

        $response->assertNoContent();

        $this->assertDatabaseMissing('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_editor_cannot_delete_collection(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(
            '/api/v1/collections/'.$collection->slug,
            [],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_rejects_duplicate_slug_on_create(): void
    {
        Collection::factory()->create(['slug' => 'taken']);

        $response = $this->postJson(
            '/api/v1/collections',
            [
                'name' => 'Other',
                'slug' => 'taken',
            ],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }
}
