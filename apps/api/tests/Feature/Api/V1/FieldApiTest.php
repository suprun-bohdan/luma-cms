<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Content\Enums\FieldType;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class FieldApiTest extends TestCase
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

        $response = $this->getJson('/api/v1/collections/'.$collection->slug.'/fields');

        $response->assertUnauthorized();
    }

    public function test_admin_lists_fields_sorted_by_sort_order(): void
    {
        $collection = Collection::factory()->create();
        Field::factory()->for($collection)->create([
            'name' => 'Second',
            'slug' => 'second',
            'sort_order' => 2,
        ]);
        Field::factory()->for($collection)->create([
            'name' => 'First',
            'slug' => 'first',
            'sort_order' => 1,
        ]);

        $response = $this->getJson(
            '/api/v1/collections/'.$collection->slug.'/fields',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'first')
            ->assertJsonPath('data.1.slug', 'second');
    }

    public function test_admin_creates_field_with_auto_slug(): void
    {
        $collection = Collection::factory()->create(['schema_version' => 1]);

        $response = $this->postJson(
            '/api/v1/collections/'.$collection->slug.'/fields',
            [
                'name' => 'Page Title',
                'type' => FieldType::Text->value,
                'required' => true,
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Page Title')
            ->assertJsonPath('data.slug', 'page-title')
            ->assertJsonPath('data.type', 'text')
            ->assertJsonPath('data.required', true);

        $this->assertDatabaseHas('fields', [
            'collection_id' => $collection->id,
            'slug' => 'page-title',
        ]);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'schema_version' => 2,
        ]);
    }

    public function test_admin_shows_field_by_slug(): void
    {
        $collection = Collection::factory()->create();
        $field = Field::factory()->for($collection)->create([
            'name' => 'Body',
            'slug' => 'body',
            'type' => FieldType::Textarea,
        ]);

        $response = $this->getJson(
            '/api/v1/collections/'.$collection->slug.'/fields/'.$field->slug,
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.slug', 'body')
            ->assertJsonPath('data.type', 'textarea');
    }

    public function test_admin_updates_field_and_bumps_schema_version(): void
    {
        $collection = Collection::factory()->create(['schema_version' => 1]);
        $field = Field::factory()->for($collection)->create([
            'name' => 'Title',
            'slug' => 'title',
            'type' => FieldType::Text,
            'required' => false,
        ]);

        $response = $this->putJson(
            '/api/v1/collections/'.$collection->slug.'/fields/'.$field->slug,
            [
                'required' => true,
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.required', true);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'schema_version' => 2,
        ]);
    }

    public function test_admin_deletes_field_and_bumps_schema_version(): void
    {
        $collection = Collection::factory()->create(['schema_version' => 1]);
        $field = Field::factory()->for($collection)->create();

        $response = $this->deleteJson(
            '/api/v1/collections/'.$collection->slug.'/fields/'.$field->slug,
            [],
            $this->withBearer($this->adminUser()),
        );

        $response->assertNoContent();

        $this->assertDatabaseMissing('fields', [
            'id' => $field->id,
        ]);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'schema_version' => 2,
        ]);
    }

    public function test_rejects_duplicate_slug_in_collection(): void
    {
        $collection = Collection::factory()->create();
        Field::factory()->for($collection)->create(['slug' => 'taken']);

        $response = $this->postJson(
            '/api/v1/collections/'.$collection->slug.'/fields',
            [
                'name' => 'Other',
                'slug' => 'taken',
                'type' => FieldType::Text->value,
            ],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_rejects_invalid_field_type(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(
            '/api/v1/collections/'.$collection->slug.'/fields',
            [
                'name' => 'Bad',
                'type' => 'invalid-type',
            ],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }

    public function test_editor_cannot_delete_field(): void
    {
        $collection = Collection::factory()->create();
        $field = Field::factory()->for($collection)->create();

        $response = $this->deleteJson(
            '/api/v1/collections/'.$collection->slug.'/fields/'.$field->slug,
            [],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('fields', [
            'id' => $field->id,
        ]);
    }

    public function test_field_from_other_collection_returns_not_found(): void
    {
        $collection = Collection::factory()->create(['slug' => 'pages']);
        $otherCollection = Collection::factory()->create(['slug' => 'posts']);
        $field = Field::factory()->for($otherCollection)->create(['slug' => 'title']);

        $response = $this->getJson(
            '/api/v1/collections/'.$collection->slug.'/fields/'.$field->slug,
            $this->withBearer($this->adminUser()),
        );

        $response->assertNotFound();
    }
}
