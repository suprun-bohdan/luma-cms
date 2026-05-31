<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Seo\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    public function definition(): array
    {
        return [
            'from_path' => '/'.fake()->unique()->slug(),
            'to_path' => '/p/home',
            'to_url' => null,
            'status_code' => 301,
            'is_active' => true,
        ];
    }
}
