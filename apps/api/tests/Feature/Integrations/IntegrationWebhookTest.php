<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Integrations\Enums\IntegrationEvent;
use App\Modules\Integrations\Enums\WebhookDeliveryStatus;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\AuthenticatesApiUsers;
use Tests\TestCase;

final class IntegrationWebhookTest extends TestCase
{
    use AuthenticatesApiUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_admin_creates_webhook(): void
    {
        $response = $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'CRM hook',
                'url' => 'https://example.com/hooks/luma',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        );

        $response
            ->assertCreated()
            ->assertJsonFragment([
                'name' => 'CRM hook',
                'is_active' => true,
            ]);

        $this->assertDatabaseHas('webhooks', [
            'name' => 'CRM hook',
            'url' => 'https://example.com/hooks/luma',
        ]);
    }

    public function test_publish_page_dispatches_webhook_delivery(): void
    {
        Http::fake([
            'https://example.com/hooks/luma' => Http::response('ok', 200),
        ]);

        $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Publish hook',
                'url' => 'https://example.com/hooks/luma',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $page = Page::query()->create([
            'title' => 'Webhook page',
            'slug' => 'webhook-page',
            'status' => PageStatus::Draft,
            'template' => 'default-page',
            'content' => ['blocks' => []],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $this->postJson(
            '/api/v1/pages/webhook-page/publish',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $this->assertDatabaseHas('webhook_deliveries', [
            'event' => IntegrationEvent::PagePublished,
            'status' => WebhookDeliveryStatus::Success->value,
        ]);

        Http::assertSentCount(1);
    }

    public function test_form_submission_emits_webhook(): void
    {
        Http::fake([
            'https://example.com/hooks/forms' => Http::response('accepted', 202),
        ]);

        $this->seed(ContactFormSeeder::class);

        $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Form hook',
                'url' => 'https://example.com/hooks/forms',
                'events' => [IntegrationEvent::FormSubmissionCreated],
            ],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $this->post('/public/forms/contact/submit', [
            '_token' => csrf_token(),
            '_hp' => '',
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello webhook',
        ])->assertRedirect();

        $this->assertDatabaseHas('webhook_deliveries', [
            'event' => IntegrationEvent::FormSubmissionCreated,
            'status' => WebhookDeliveryStatus::Success->value,
        ]);
    }

    public function test_admin_lists_deliveries_and_retries_failed_delivery(): void
    {
        Http::fake([
            'https://example.com/hooks/retry' => Http::sequence()
                ->push('fail', 500)
                ->push('ok', 200),
        ]);

        $create = $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Retry hook',
                'url' => 'https://example.com/hooks/retry',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $webhookId = $create->json('id');

        Page::query()->create([
            'title' => 'Retry page',
            'slug' => 'retry-page',
            'status' => PageStatus::Draft,
            'template' => 'default-page',
            'content' => ['blocks' => []],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $this->postJson(
            '/api/v1/pages/retry-page/publish',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $delivery = WebhookDelivery::query()->firstOrFail();
        $this->assertSame(WebhookDeliveryStatus::Pending, $delivery->status);

        $this->postJson(
            "/api/v1/integrations/webhooks/{$webhookId}/deliveries/{$delivery->id}/retry",
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk()
            ->assertJsonFragment(['status' => 'success']);
    }

    public function test_rejects_http_webhook_url(): void
    {
        $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Insecure',
                'url' => 'http://example.com/hook',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        )->assertStatus(422);
    }
}
