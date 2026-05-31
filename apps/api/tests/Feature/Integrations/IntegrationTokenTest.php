<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Integrations\Services\IntegrationScopeCatalog;
use App\Modules\Integrations\Services\IntegrationTokenService;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class IntegrationTokenTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_creates_integration_token_once(): void
    {
        $response = $this->postJson(
            '/api/v1/integrations/tokens',
            [
                'name' => 'Headless reader',
                'abilities' => [IntegrationScopeCatalog::ContentRead],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'token_prefix', 'plain_text_token', 'abilities'],
            ]);

        $this->assertDatabaseHas('integration_tokens', [
            'name' => 'Headless reader',
        ]);
    }

    public function test_integration_token_reads_published_page_with_scope(): void
    {
        Page::query()->create([
            'title' => 'Public integration page',
            'slug' => 'integration-public',
            'status' => PageStatus::Published,
            'template' => 'default-page',
            'content' => ['blocks' => []],
            'published_at' => now(),
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $created = app(IntegrationTokenService::class)->create(
            'Reader',
            [IntegrationScopeCatalog::ContentRead],
            $this->adminUser(),
        );

        $this->getJson(
            '/api/v1/integration/pages/integration-public',
            ['Authorization' => 'Bearer '.$created['plain_text']],
        )
            ->assertOk()
            ->assertJsonFragment(['slug' => 'integration-public']);
    }

    public function test_integration_token_without_scope_is_forbidden(): void
    {
        $this->seed(ContactFormSeeder::class);

        $created = app(IntegrationTokenService::class)->create(
            'Content only',
            [IntegrationScopeCatalog::ContentRead],
            $this->adminUser(),
        );

        $this->getJson(
            '/api/v1/integration/forms/contact/submissions',
            ['Authorization' => 'Bearer '.$created['plain_text']],
        )->assertForbidden();
    }

    public function test_integration_token_reads_form_submissions_with_scope(): void
    {
        $this->seed(ContactFormSeeder::class);

        $this->post('/public/forms/contact/submit', [
            '_token' => csrf_token(),
            '_hp' => '',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Need help',
        ])->assertRedirect();

        $created = app(IntegrationTokenService::class)->create(
            'Forms inbox',
            [IntegrationScopeCatalog::FormsReadSubmissions],
            $this->adminUser(),
        );

        $this->getJson(
            '/api/v1/integration/forms/contact/submissions',
            ['Authorization' => 'Bearer '.$created['plain_text']],
        )
            ->assertOk()
            ->assertJsonFragment(['email' => 'ada@example.com']);
    }

    public function test_integration_token_cannot_access_admin_routes(): void
    {
        $created = app(IntegrationTokenService::class)->create(
            'Reader',
            [IntegrationScopeCatalog::ContentRead],
            $this->adminUser(),
        );

        $this->getJson(
            '/api/v1/collections',
            ['Authorization' => 'Bearer '.$created['plain_text']],
        )->assertUnauthorized();
    }

    public function test_editor_cannot_create_integration_tokens(): void
    {
        $this->postJson(
            '/api/v1/integrations/tokens',
            [
                'name' => 'Blocked',
                'abilities' => [IntegrationScopeCatalog::ContentRead],
            ],
            $this->withBearer($this->editorUser()),
        )->assertForbidden();
    }
}
