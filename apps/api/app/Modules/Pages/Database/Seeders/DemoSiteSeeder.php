<?php

declare(strict_types=1);

namespace App\Modules\Pages\Database\Seeders;

use App\Models\User;
use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Navigation\Models\MenuItem;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Database\Seeder;

class DemoSiteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', env('LUMA_SEED_ADMIN_EMAIL', 'admin@luma.test'))->first();

        if ($admin === null) {
            return;
        }

        $this->call(ContactFormSeeder::class);

        $page = Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Welcome to Luma',
                'status' => PageStatus::Published,
                'template' => 'default-page',
                'content' => [
                    'blocks' => [
                        [
                            'id' => 'hero-1',
                            'type' => 'hero',
                            'props' => [
                                'headline' => 'Build your business site with Luma',
                                'subheadline' => 'Structured content, pages, and navigation — without lock-in.',
                            ],
                        ],
                        [
                            'id' => 'text-1',
                            'type' => 'rich_text',
                            'props' => [
                                'body' => 'Edit this page in Studio, publish when ready, and share the public URL with your team.',
                            ],
                        ],
                        [
                            'id' => 'cta-1',
                            'type' => 'cta',
                            'props' => [
                                'label' => 'Open Studio',
                                'url' => '/dashboard',
                            ],
                        ],
                        [
                            'id' => 'contact-1',
                            'type' => 'contact_form',
                            'props' => [
                                'form_slug' => 'contact',
                                'title' => 'Contact us',
                                'submit_label' => 'Send message',
                            ],
                        ],
                    ],
                ],
                'seo' => [
                    'title' => 'Welcome to Luma CMS',
                    'description' => 'Demo landing page seeded for local onboarding.',
                ],
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        );

        $header = Menu::query()->updateOrCreate(
            ['slug' => 'header'],
            ['name' => 'Header'],
        );

        MenuItem::query()->where('menu_id', $header->id)->delete();
        MenuItem::query()->create([
            'menu_id' => $header->id,
            'label' => 'Home',
            'page_slug' => $page->slug,
            'sort_order' => 0,
        ]);

        $footer = Menu::query()->updateOrCreate(
            ['slug' => 'footer'],
            ['name' => 'Footer'],
        );

        MenuItem::query()->where('menu_id', $footer->id)->delete();
        MenuItem::query()->create([
            'menu_id' => $footer->id,
            'label' => 'Luma CMS',
            'url' => 'https://github.com/suprun-bohdan/luma-cms',
            'sort_order' => 0,
        ]);
    }
}
