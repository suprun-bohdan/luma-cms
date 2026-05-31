<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations;

use App\Modules\Forms\Database\Seeders\ContactFormSeeder;
use App\Modules\Integrations\Enums\IntegrationEvent;
use App\Modules\Integrations\Enums\WebhookDeliveryStatus;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Pages\Enums\PageStatus;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $this->assertSame(1, $delivery->attempts);

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

    public function test_publish_succeeds_when_webhook_dispatch_fails(): void
    {
        Bus::shouldReceive('dispatch')->andThrow(new \RuntimeException('Queue unavailable'));

        $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Dispatch fail hook',
                'url' => 'https://example.com/hooks/dispatch-fail',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $page = Page::query()->create([
            'title' => 'Dispatch fail page',
            'slug' => 'dispatch-fail-page',
            'status' => PageStatus::Draft,
            'template' => 'default-page',
            'content' => ['blocks' => []],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $this->postJson(
            '/api/v1/pages/dispatch-fail-page/publish',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk()
            ->assertJsonPath('status', PageStatus::Published->value);

        $this->assertDatabaseHas('webhook_deliveries', [
            'event' => IntegrationEvent::PagePublished,
            'status' => WebhookDeliveryStatus::Pending->value,
        ]);
    }

    public function test_failed_webhook_delivery_records_error_details(): void
    {
        Http::fake([
            'https://example.com/hooks/failed' => Http::response('server error', 500),
        ]);

        $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Failed hook',
                'url' => 'https://example.com/hooks/failed',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        Page::query()->create([
            'title' => 'Failed delivery page',
            'slug' => 'failed-delivery-page',
            'status' => PageStatus::Draft,
            'template' => 'default-page',
            'content' => ['blocks' => []],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $this->postJson(
            '/api/v1/pages/failed-delivery-page/publish',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        $delivery = WebhookDelivery::query()->firstOrFail();

        $this->assertGreaterThanOrEqual(1, $delivery->attempts);
        $this->assertNotNull($delivery->error_message);
        $this->assertSame(500, $delivery->response_status);
    }

    public function test_webhook_failure_logs_do_not_include_secret(): void
    {
        Bus::shouldReceive('dispatch')->andThrow(new \RuntimeException('Queue unavailable'));

        Log::spy();

        $this->postJson(
            '/api/v1/integrations/webhooks',
            [
                'name' => 'Secret log hook',
                'url' => 'https://example.com/hooks/secret-log',
                'events' => [IntegrationEvent::PagePublished],
            ],
            $this->withBearer($this->adminUser()),
        )->assertCreated();

        $webhook = Webhook::query()->where('name', 'Secret log hook')->firstOrFail();
        $secret = $webhook->secret;

        Page::query()->create([
            'title' => 'Secret log page',
            'slug' => 'secret-log-page',
            'status' => PageStatus::Draft,
            'template' => 'default-page',
            'content' => ['blocks' => []],
            'created_by' => $this->adminUser()->id,
            'updated_by' => $this->adminUser()->id,
        ]);

        $this->postJson(
            '/api/v1/pages/secret-log-page/publish',
            [],
            $this->withBearer($this->adminUser()),
        )->assertOk();

        Log::shouldHaveReceived('warning')
            ->withArgs(function (string $message, array $context) use ($secret): bool {
                if ($message !== 'Webhook delivery dispatch failed') {
                    return false;
                }

                $encoded = json_encode($context);

                return is_string($encoded) && ! str_contains($encoded, $secret);
            })
            ->atLeast()
            ->once();
    }
}
