<?php

declare(strict_types=1);

namespace App\Modules\Pages\Services;

use App\Models\User;
use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Navigation\Models\MenuItem;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use InvalidArgumentException;

final class StarterSiteService
{
    /** @var array<string, array<string, mixed>> */
    private array $presets = [
        'business' => [
            'title' => 'Welcome to Luma',
            'slug' => 'home',
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
                        'url' => '/admin/',
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
        'blog' => [
            'title' => 'Latest from our blog',
            'slug' => 'home',
            'blocks' => [
                [
                    'id' => 'hero-1',
                    'type' => 'hero',
                    'props' => [
                        'headline' => 'Stories, updates, and ideas',
                        'subheadline' => 'Publish articles and keep readers coming back.',
                    ],
                ],
                [
                    'id' => 'text-1',
                    'type' => 'rich_text',
                    'props' => [
                        'body' => 'Replace this block with your latest posts or link to a blog collection.',
                    ],
                ],
            ],
        ],
        'portfolio' => [
            'title' => 'Selected work',
            'slug' => 'home',
            'blocks' => [
                [
                    'id' => 'hero-1',
                    'type' => 'hero',
                    'props' => [
                        'headline' => 'Showcase your best projects',
                        'subheadline' => 'A clean landing page for creatives and agencies.',
                    ],
                ],
                [
                    'id' => 'text-1',
                    'type' => 'rich_text',
                    'props' => [
                        'body' => 'Add project highlights, case studies, or embed media from the library.',
                    ],
                ],
                [
                    'id' => 'cta-1',
                    'type' => 'cta',
                    'props' => [
                        'label' => 'Get in touch',
                        'url' => '/p/home#contact',
                    ],
                ],
            ],
        ],
    ];

    public function installPreset(string $preset, User $admin): Page
    {
        if (! isset($this->presets[$preset])) {
            throw new InvalidArgumentException("Unknown starter preset: {$preset}");
        }

        $config = $this->presets[$preset];

        (new ContactFormSeeder())->run();

        $page = Page::query()->updateOrCreate(
            ['slug' => $config['slug']],
            [
                'title' => $config['title'],
                'status' => PageStatus::Published,
                'template' => 'default-page',
                'content' => ['blocks' => $config['blocks']],
                'seo' => [
                    'title' => $config['title'],
                    'description' => 'Starter site generated during installation.',
                ],
                'published_at' => now(),
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ],
        );

        $header = Menu::query()->updateOrCreate(['slug' => 'header'], ['name' => 'Header']);
        MenuItem::query()->where('menu_id', $header->id)->delete();
        MenuItem::query()->create([
            'menu_id' => $header->id,
            'label' => 'Home',
            'page_slug' => $page->slug,
            'sort_order' => 0,
        ]);

        $footer = Menu::query()->updateOrCreate(['slug' => 'footer'], ['name' => 'Footer']);
        MenuItem::query()->where('menu_id', $footer->id)->delete();
        MenuItem::query()->create([
            'menu_id' => $footer->id,
            'label' => 'Luma CMS',
            'url' => 'https://github.com/suprun-bohdan/luma-cms',
            'sort_order' => 0,
        ]);

        return $page;
    }
}
