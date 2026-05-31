<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Modules\Pages\Database\Seeders\DemoSiteSeeder;
use App\Modules\Seo\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class RedirectApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
        $this->seed(DemoSiteSeeder::class);
    }

    public function test_unauthenticated_list_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/redirects');

        $response->assertUnauthorized();
    }

    public function test_admin_creates_redirect(): void
    {
        $response = $this->postJson(
            '/api/v1/redirects',
            [
                'from_path' => '/legacy-home',
                'to_path' => '/p/home',
                'status_code' => 301,
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonPath('from_path', '/legacy-home')
            ->assertJsonPath('to_path', '/p/home')
            ->assertJsonPath('status_code', 301);

        $this->assertDatabaseHas('redirects', [
            'from_path' => '/legacy-home',
            'to_path' => '/p/home',
        ]);
    }

    public function test_admin_lists_redirects(): void
    {
        Redirect::factory()->create(['from_path' => '/a']);

        $response = $this->getJson(
            '/api/v1/redirects',
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_editor_cannot_create_redirect(): void
    {
        $response = $this->postJson(
            '/api/v1/redirects',
            [
                'from_path' => '/blocked',
                'to_path' => '/p/home',
            ],
            $this->withBearer($this->editorUser()),
        );

        $response->assertForbidden();
    }

    public function test_rejects_both_to_path_and_to_url(): void
    {
        $response = $this->postJson(
            '/api/v1/redirects',
            [
                'from_path' => '/bad',
                'to_path' => '/p/home',
                'to_url' => 'https://example.com',
            ],
            $this->withBearer($this->adminUser()),
        );

        $response->assertUnprocessable();
    }
}
