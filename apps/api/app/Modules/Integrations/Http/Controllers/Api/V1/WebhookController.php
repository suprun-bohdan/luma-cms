<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Http\Requests\StoreWebhookRequest;
use App\Modules\Integrations\Http\Requests\UpdateWebhookRequest;
use App\Modules\Integrations\Http\Resources\WebhookResource;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Services\IntegrationEventCatalog;
use App\Modules\Integrations\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WebhookController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Webhook::class);

        $webhooks = Webhook::query()->orderBy('name')->get();

        return WebhookResource::collection($webhooks);
    }

    public function store(StoreWebhookRequest $request, WebhookService $service): JsonResponse
    {
        $this->authorize('create', Webhook::class);

        $webhook = $service->create(
            $request->validated('name'),
            $request->validated('url'),
            $request->validated('events'),
            $request->user(),
            $request->boolean('is_active', true),
        );

        return (new WebhookResource($webhook))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Webhook $webhook): WebhookResource
    {
        $this->authorize('view', $webhook);

        return new WebhookResource($webhook);
    }

    public function update(
        UpdateWebhookRequest $request,
        Webhook $webhook,
        WebhookService $service,
    ): WebhookResource {
        $this->authorize('update', $webhook);

        return new WebhookResource(
            $service->update($webhook, $request->validated(), $request->user()),
        );
    }

    public function destroy(Webhook $webhook, WebhookService $service): JsonResponse
    {
        $this->authorize('delete', $webhook);

        $service->delete($webhook, request()->user());

        return response()->json(null, 204);
    }

    public function events(IntegrationEventCatalog $catalog): JsonResponse
    {
        $this->authorize('viewAny', Webhook::class);

        return response()->json([
            'data' => $catalog->all(),
        ]);
    }
}
