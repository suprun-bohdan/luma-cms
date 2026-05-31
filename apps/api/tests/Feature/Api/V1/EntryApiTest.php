<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Enums\FieldType;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use App\Modules\Content\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class EntryApiTest extends TestCase
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
        $collection = Collection::factory()->create();

        $response = $this->getJson('/api/v1/collections/'.$collection->slug.'/entries');

        $response->assertUnauthorized();
    }

    public function test_admin_creates_draft_entry(): void
    {
        $collection = $this->collectionWithFields();

        $response = $this->postJson(
            '/api/v1/collections/'.$collection->slug.'/entries',
            ['data' => ['title' => 'Hello World']],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('data.title', 'Hello World')
            ->assertJsonPath('published_at', null);

        $this->assertDatabaseHas('entries', [
            'collection_id' => $collection->id,
            'status' => EntryStatus::Draft->value,
        ]);
    }

    public function test_rejects_unknown_data_keys(): void
    {
        $collection = $this->collectionWithFields();

        $response = $this->postJson(
            '/api/v1/collections/'.$collection->slug.'/entries',
            ['data' => ['title' => 'Ok', 'unknown' => 'bad']],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_rejects_missing_required_field_on_create(): void
    {
        $collection = Collection::factory()->create(['slug' => 'posts']);
        Field::factory()->for($collection)->create([
            'slug' => 'title',
            'type' => FieldType::Text,
            'required' => true,
        ]);

        $response = $this->postJson(
            '/api/v1/collections/'.$collection->slug.'/entries',
            ['data' => []],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_admin_updates_entry(): void
    {
        $collection = $this->collectionWithFields();
        $entry = Entry::factory()->for($collection)->create([
            'data' => ['title' => 'Old'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->putJson(
            '/api/v1/entries/'.$entry->id,
            ['data' => ['title' => 'New']],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'New');
    }

    public function test_publish_creates_entry_version_and_sets_published_at(): void
    {
        $collection = $this->collectionWithFields(['schema_version' => 3]);
        $entry = Entry::factory()->for($collection)->create([
            'data' => ['title' => 'Ready to publish'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->postJson(
            '/api/v1/entries/'.$entry->id.'/publish',
            [],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('status', 'published');

        $this->assertDatabaseHas('entries', [
            'id' => $entry->id,
            'status' => EntryStatus::Published->value,
        ]);

        $this->assertDatabaseHas('entry_versions', [
            'entry_id' => $entry->id,
            'schema_version' => 3,
        ]);
    }

    public function test_publish_fails_if_required_field_missing(): void
    {
        $collection = Collection::factory()->create(['slug' => 'articles']);
        Field::factory()->for($collection)->create([
            'slug' => 'title',
            'type' => FieldType::Text,
            'required' => true,
        ]);

        $entry = Entry::factory()->for($collection)->create([
            'data' => ['title' => 'Has title'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $this->putJson(
            '/api/v1/entries/'.$entry->id,
            ['data' => []],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $response = $this->postJson(
            '/api/v1/entries/'.$entry->id.'/publish',
            [],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_unpublish_returns_to_draft(): void
    {
        $collection = $this->collectionWithFields();
        $entry = Entry::factory()->for($collection)->published()->create([
            'data' => ['title' => 'Published'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->postJson(
            '/api/v1/entries/'.$entry->id.'/unpublish',
            [],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('published_at', null);
    }

    public function test_editor_cannot_delete_entry(): void
    {
        $collection = $this->collectionWithFields();
        $entry = Entry::factory()->for($collection)->create([
            'data' => ['title' => 'Keep me'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->deleteJson(
            '/api/v1/entries/'.$entry->id,
            [],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('entries', ['id' => $entry->id]);
    }

    public function test_filters_entries_by_status(): void
    {
        $collection = $this->collectionWithFields();

        Entry::factory()->for($collection)->create([
            'status' => EntryStatus::Draft,
            'data' => ['title' => 'Draft'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        Entry::factory()->for($collection)->published()->create([
            'data' => ['title' => 'Published'],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $response = $this->getJson(
            '/api/v1/collections/'.$collection->slug.'/entries?status=published',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'published');
    }

    /**
     * @param  array<string, mixed>  $collectionAttributes
     */
    private function collectionWithFields(array $collectionAttributes = []): Collection
    {
        $collection = Collection::factory()->create($collectionAttributes);

        Field::factory()->for($collection)->create([
            'name' => 'Title',
            'slug' => 'title',
            'type' => FieldType::Text,
            'required' => false,
        ]);

        return $collection;
    }
}
