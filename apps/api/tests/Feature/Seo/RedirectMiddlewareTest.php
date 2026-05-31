<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Modules\Seo\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RedirectMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_redirect_returns_301(): void
    {
        Redirect::query()->create([
            'from_path' => '/legacy-home',
            'to_path' => '/p/home',
            'to_url' => null,
            'status_code' => 301,
            'is_active' => true,
        ]);

        $response = $this->get('/legacy-home');

        $response->assertRedirect('/p/home');
        $response->assertStatus(301);
    }

    public function test_inactive_redirect_is_ignored(): void
    {
        Redirect::query()->create([
            'from_path' => '/legacy-home',
            'to_path' => '/p/home',
            'to_url' => null,
            'status_code' => 301,
            'is_active' => false,
        ]);

        $response = $this->get('/legacy-home');

        $response->assertNotFound();
    }

    public function test_external_redirect_uses_away(): void
    {
        Redirect::query()->create([
            'from_path' => '/external',
            'to_path' => null,
            'to_url' => 'https://example.com/help',
            'status_code' => 302,
            'is_active' => true,
        ]);

        $response = $this->get('/external');

        $response->assertRedirect('https://example.com/help');
        $response->assertStatus(302);
    }
}
