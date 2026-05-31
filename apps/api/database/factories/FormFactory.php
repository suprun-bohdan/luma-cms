<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Forms\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        $slug = fake()->unique()->slug();

        return [
            'name' => fake()->words(2, true),
            'slug' => $slug,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
