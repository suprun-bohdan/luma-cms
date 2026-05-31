<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Jobs;

use App\Modules\Integrations\Enums\WebhookDeliveryStatus;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Integrations\Services\WebhookSignatureService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class DeliverWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [30, 120, 600];

    public function __construct(
        public readonly string $deliveryId,
    ) {
        $this->onQueue('webhooks');
    }

    public function handle(WebhookSignatureService $signatures): void
    {
        $delivery = WebhookDelivery::query()
            ->with('webhook')
            ->find($this->deliveryId);

        if ($delivery === null || $delivery->webhook === null) {
            return;
        }

        if ($delivery->status === WebhookDeliveryStatus::Success) {
            return;
        }

        $webhook = $delivery->webhook;
        $payload = $delivery->payload ?? [];
        $payload['delivery_id'] = $delivery->id;

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $headers = $signatures->headers($webhook->secret, $body);

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->withBody($body, 'application/json')
                ->post($webhook->url);

            $responseBody = substr((string) $response->body(), 0, 2000);

            if ($response->successful()) {
                $delivery->update([
                    'status' => WebhookDeliveryStatus::Success,
                    'attempts' => $this->attempts(),
                    'response_status' => $response->status(),
                    'response_body' => $responseBody,
                    'error_message' => null,
                    'next_retry_at' => null,
                    'delivered_at' => now(),
                ]);

                return;
            }

            $this->markRetryableFailure(
                $delivery,
                $response->status(),
                $responseBody,
                'HTTP '.$response->status(),
            );
        } catch (Throwable $exception) {
            if ($exception instanceof RuntimeException && str_starts_with($exception->getMessage(), 'HTTP ')) {
                throw $exception;
            }

            Log::warning('Webhook delivery failed', [
                'delivery_id' => $delivery->id,
                'webhook_id' => $webhook->id,
                'message' => $exception->getMessage(),
            ]);

            $this->markRetryableFailure($delivery, null, null, $exception->getMessage());
        }
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = WebhookDelivery::query()->find($this->deliveryId);

        if ($delivery === null || $delivery->status === WebhookDeliveryStatus::Success) {
            return;
        }

        if ($this->attempts() < $this->tries) {
            return;
        }

        if ($delivery->status === WebhookDeliveryStatus::Failed) {
            return;
        }

        $delivery->update([
            'status' => WebhookDeliveryStatus::Failed,
            'attempts' => $this->attempts(),
            'error_message' => substr($exception?->getMessage() ?? 'Delivery failed', 0, 1000),
            'next_retry_at' => null,
        ]);
    }

    private function markRetryableFailure(
        WebhookDelivery $delivery,
        ?int $responseStatus,
        ?string $responseBody,
        string $errorMessage,
    ): void {
        $attempt = $this->attempts();
        $delay = $this->backoff[min(max($attempt - 1, 0), count($this->backoff) - 1)] ?? 600;

        if ($attempt >= $this->tries) {
            $delivery->update([
                'status' => WebhookDeliveryStatus::Failed,
                'attempts' => $attempt,
                'response_status' => $responseStatus,
                'response_body' => $responseBody,
                'error_message' => substr($errorMessage, 0, 1000),
                'next_retry_at' => null,
                'delivered_at' => null,
            ]);

            return;
        }

        $delivery->update([
            'status' => WebhookDeliveryStatus::Pending,
            'attempts' => $attempt,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'error_message' => substr($errorMessage, 0, 1000),
            'next_retry_at' => now()->addSeconds($delay),
            'delivered_at' => null,
        ]);

        throw new RuntimeException($errorMessage);
    }
}
