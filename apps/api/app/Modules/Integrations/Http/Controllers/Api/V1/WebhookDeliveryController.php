<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Actions\RetryWebhookDeliveryAction;
use App\Modules\Integrations\Http\Resources\WebhookDeliveryResource;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WebhookDeliveryController extends Controller
{
    public function index(Webhook $webhook): AnonymousResourceCollection
    {
        $this->authorize('viewDeliveries', $webhook);

        $deliveries = $webhook->deliveries()
            ->orderByDesc('created_at')
            ->paginate(50);

        return WebhookDeliveryResource::collection($deliveries);
    }

    public function retry(
        Webhook $webhook,
        string $delivery,
        RetryWebhookDeliveryAction $action,
    ): WebhookDeliveryResource {
        $deliveryModel = WebhookDelivery::query()
            ->where('webhook_id', $webhook->id)
            ->where('id', $delivery)
            ->firstOrFail();

        $this->authorize('retryDelivery', [$webhook, $deliveryModel]);

        return new WebhookDeliveryResource($action->execute($deliveryModel));
    }
}
