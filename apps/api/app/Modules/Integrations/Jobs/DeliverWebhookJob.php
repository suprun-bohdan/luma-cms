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
                    'attempts' => $delivery->attempts + 1,
                    'response_status' => $response->status(),
                    'response_body' => $responseBody,
                    'error_message' => null,
                    'next_retry_at' => null,
                    'delivered_at' => now(),
                ]);

                return;
            }

            $this->markFailure(
                $delivery,
                $response->status(),
                $responseBody,
                'HTTP '.$response->status(),
            );
        } catch (Throwable $exception) {
            Log::warning('Webhook delivery failed', [
                'delivery_id' => $delivery->id,
                'webhook_id' => $webhook->id,
                'message' => $exception->getMessage(),
            ]);

            $this->markFailure($delivery, null, null, $exception->getMessage());
        }
    }

    private function markFailure(
        WebhookDelivery $delivery,
        ?int $responseStatus,
        ?string $responseBody,
        string $errorMessage,
    ): void {
        $attempts = $delivery->attempts + 1;
        $isFinal = $attempts >= $this->tries;

        $delivery->update([
            'status' => $isFinal ? WebhookDeliveryStatus::Failed : WebhookDeliveryStatus::Pending,
            'attempts' => $attempts,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'error_message' => substr($errorMessage, 0, 1000),
            'next_retry_at' => $isFinal ? null : now()->addSeconds($this->backoff[min($attempts - 1, count($this->backoff) - 1)] ?? 600),
            'delivered_at' => null,
        ]);

        if (! $isFinal && config('queue.connections.'.config('queue.default').'.driver') !== 'sync') {
            $delay = $this->backoff[min($attempts - 1, count($this->backoff) - 1)] ?? 600;
            self::dispatch($delivery->id)
                ->onQueue('webhooks')
                ->delay(now()->addSeconds($delay));
        }
    }
}
