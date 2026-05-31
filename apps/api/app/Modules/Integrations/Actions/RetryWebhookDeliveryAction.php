<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Actions;

use App\Modules\Integrations\Enums\WebhookDeliveryStatus;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use App\Modules\Integrations\Models\WebhookDelivery;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RetryWebhookDeliveryAction
{
    public function execute(WebhookDelivery $delivery): WebhookDelivery
    {
        if ($delivery->status === WebhookDeliveryStatus::Success) {
            return $delivery;
        }

        $delivery->update([
            'status' => WebhookDeliveryStatus::Pending,
            'attempts' => 0,
            'response_status' => null,
            'response_body' => null,
            'error_message' => null,
            'next_retry_at' => null,
            'delivered_at' => null,
        ]);

        try {
            DeliverWebhookJob::dispatchSync($delivery->id);
        } catch (Throwable $exception) {
            Log::warning('Manual webhook retry failed', [
                'delivery_id' => $delivery->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return $delivery->refresh();
    }
}
