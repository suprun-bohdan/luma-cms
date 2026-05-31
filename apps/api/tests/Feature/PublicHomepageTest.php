<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use App\Modules\Setup\Models\LumaInstallation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicHomepageTest extends TestCase
{
    use RefreshDatabase;
    public function test_web_root_redirects_to_published_home_page_when_installed(): void
    {
        LumaInstallation::query()->create([
            'completed_at' => now(),
            'version' => '0.0.25-rc.13',
        ]);

        Page::factory()->create([
            'slug' => 'home',
            'status' => PageStatus::Published,
            'published_at' => now(),
        ]);

        $this->get('/')
            ->assertRedirect('/p/home');
    }
}
