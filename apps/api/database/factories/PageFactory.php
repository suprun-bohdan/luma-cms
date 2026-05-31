<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'status' => PageStatus::Draft,
            'template' => 'default-page',
            'content' => [
                'blocks' => [
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'rich_text',
                        'props' => ['body' => fake()->paragraph()],
                    ],
                ],
            ],
            'seo' => null,
            'published_at' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PageStatus::Published,
            'published_at' => now(),
        ]);
    }
}
