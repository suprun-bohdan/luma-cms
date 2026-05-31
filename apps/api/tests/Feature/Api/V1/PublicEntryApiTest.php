<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Enums\FieldType;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use App\Modules\Content\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicEntryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_list_returns_only_published_entries(): void
    {
        $collection = Collection::factory()->create(['slug' => 'blog']);

        Field::factory()->for($collection)->create([
            'slug' => 'title',
            'type' => FieldType::Text,
        ]);

        Entry::factory()->for($collection)->create([
            'status' => EntryStatus::Draft,
            'data' => ['title' => 'Draft post'],
        ]);

        Entry::factory()->for($collection)->published()->create([
            'data' => ['title' => 'Published post'],
        ]);

        $response = $this->getJson('/api/v1/public/collections/'.$collection->slug.'/entries');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data.title', 'Published post');
    }

    public function test_public_show_unpublished_entry_returns_not_found(): void
    {
        $collection = Collection::factory()->create();
        $entry = Entry::factory()->for($collection)->create([
            'status' => EntryStatus::Draft,
            'data' => [],
        ]);

        $response = $this->getJson('/api/v1/public/entries/'.$entry->id);

        $response->assertNotFound();
    }

    public function test_public_show_published_entry(): void
    {
        $collection = Collection::factory()->create();

        Field::factory()->for($collection)->create([
            'slug' => 'title',
            'type' => FieldType::Text,
        ]);

        $entry = Entry::factory()->for($collection)->published()->create([
            'data' => ['title' => 'Live post'],
        ]);

        $response = $this->getJson('/api/v1/public/entries/'.$entry->id);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'published')
            ->assertJsonPath('data.title', 'Live post');
    }
}
