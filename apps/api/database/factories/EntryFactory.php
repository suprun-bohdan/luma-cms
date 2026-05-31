<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Modules\Content\Enums\EntryStatus;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    protected $model = Entry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'status' => EntryStatus::Draft,
            'data' => [],
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => EntryStatus::Published,
            'published_at' => now(),
        ]);
    }
}
