<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Content\Enums\FieldType;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Field;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Field>
 */
class FieldFactory extends Factory
{
    protected $model = Field::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'collection_id' => Collection::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(FieldType::cases()),
            'config' => null,
            'sort_order' => 0,
            'required' => false,
        ];
    }
}
