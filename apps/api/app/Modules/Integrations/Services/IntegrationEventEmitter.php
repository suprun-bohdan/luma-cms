<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Content\Models\Entry;
use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Models\FormSubmission;
use App\Modules\Integrations\Enums\IntegrationEvent;
use App\Modules\Integrations\Enums\WebhookDeliveryStatus;
use App\Modules\Integrations\Jobs\DeliverWebhookJob;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Integrations\Models\WebhookDelivery;
use App\Modules\Pages\Models\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class IntegrationEventEmitter
{
    public function __construct(
        private readonly IntegrationEventCatalog $catalog,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function emit(string $event, string $entity, int|string $entityId, ?string $slug = null, array $data = []): void
    {
        $this->catalog->assertValid($event);

        $payload = [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'entity' => $entity,
            'id' => $entityId,
            'slug' => $slug,
            'data' => $data,
        ];

        Webhook::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->filter(static fn (Webhook $webhook): bool => $webhook->subscribesTo($event))
            ->each(function (Webhook $webhook) use ($event, $payload): void {
                $delivery = WebhookDelivery::query()->create([
                    'id' => (string) Str::uuid(),
                    'webhook_id' => $webhook->id,
                    'event' => $event,
                    'payload' => $payload,
                    'status' => WebhookDeliveryStatus::Pending,
                    'attempts' => 0,
                ]);

                try {
                    DeliverWebhookJob::dispatch($delivery->id)->onQueue('webhooks');
                } catch (Throwable $exception) {
                    Log::warning('Webhook delivery dispatch failed', [
                        'delivery_id' => $delivery->id,
                        'webhook_id' => $webhook->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });
    }

    public function pagePublished(Page $page): void
    {
        $this->emit(
            IntegrationEvent::PagePublished,
            'page',
            $page->id,
            $page->slug,
            [
                'title' => $page->title,
                'status' => $page->status->value,
                'published_at' => $page->published_at?->toIso8601String(),
            ],
        );
    }

    public function entryPublished(Entry $entry): void
    {
        $entry->loadMissing('collection');

        $this->emit(
            IntegrationEvent::EntryPublished,
            'entry',
            $entry->id,
            $entry->slug,
            [
                'collection' => $entry->collection->slug,
                'status' => $entry->status->value,
                'published_at' => $entry->published_at?->toIso8601String(),
            ],
        );
    }

    public function formSubmissionCreated(Form $form, FormSubmission $submission): void
    {
        $this->emit(
            IntegrationEvent::FormSubmissionCreated,
            'form_submission',
            $submission->id,
            $form->slug,
            [
                'form_id' => $form->id,
                'form_slug' => $form->slug,
                'submitted_at' => $submission->created_at?->toIso8601String(),
            ],
        );
    }
}
