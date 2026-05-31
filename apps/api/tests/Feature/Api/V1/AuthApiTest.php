<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class AuthApiTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_login_returns_token_and_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@luma.test',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'roles'],
                ],
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'admin@luma.test')
            ->assertJsonPath('data.user.roles', ['admin']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@luma.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->adminUser();

        $response = $this->getJson('/api/v1/auth/me', $this->withBearer($user));

        $response
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@luma.test')
            ->assertJsonPath('data.roles', ['admin']);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized();
    }

    public function test_api_routes_return_401_without_accept_json_header(): void
    {
        $response = $this->get('/api/v1/collections');

        $response->assertUnauthorized();
    }
}
