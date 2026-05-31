<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Actions;

use App\Modules\Integrations\Enums\WebhookDeliveryStatus;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use App\Modules\Integrations\Models\WebhookDelivery;

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

        DeliverWebhookJob::dispatch($delivery->id)->onQueue('webhooks');

        return $delivery->refresh();
    }
}
