<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RobotsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_includes_sitemap_reference(): void
    {
        $response = $this->get('/robots.txt');

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *', false)
            ->assertSee('Disallow: /api/', false)
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }
}
