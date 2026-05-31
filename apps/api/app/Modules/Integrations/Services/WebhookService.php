<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Models\User;
use App\Modules\Integrations\Models\Webhook;
use App\Modules\Plugins\Services\AuditLogService;
use Illuminate\Support\Str;

final class WebhookService
{
    public function __construct(
        private readonly IntegrationEventCatalog $events,
        private readonly AuditLogService $auditLog,
    ) {
    }

    /**
     * @param  list<string>  $subscribedEvents
     */
    public function create(
        string $name,
        string $url,
        array $subscribedEvents,
        User $actor,
        bool $isActive = true,
    ): Webhook {
        $this->events->assertValidMany($subscribedEvents);

        $webhook = Webhook::query()->create([
            'name' => $name,
            'url' => $url,
            'secret' => Str::random(32),
            'events' => array_values($subscribedEvents),
            'is_active' => $isActive,
            'created_by' => $actor->id,
        ]);

        $this->auditLog->record(
            'integration.webhook.created',
            'webhook',
            (string) $webhook->id,
            $actor,
            ['name' => $name, 'url' => $url, 'events' => $subscribedEvents],
        );

        return $webhook;
    }

    /**
     * @param  array{name?: string, url?: string, events?: list<string>, is_active?: bool}  $attributes
     */
    public function update(Webhook $webhook, array $attributes, User $actor): Webhook
    {
        if (array_key_exists('events', $attributes)) {
            $this->events->assertValidMany($attributes['events']);
        }

        foreach (['name', 'url', 'events', 'is_active'] as $key) {
            if (array_key_exists($key, $attributes)) {
                $webhook->{$key} = $attributes[$key];
            }
        }

        $webhook->save();

        $this->auditLog->record(
            'integration.webhook.updated',
            'webhook',
            (string) $webhook->id,
            $actor,
            ['name' => $webhook->name],
        );

        return $webhook->refresh();
    }

    public function delete(Webhook $webhook, User $actor): void
    {
        $webhookId = (string) $webhook->id;
        $name = $webhook->name;

        $webhook->delete();

        $this->auditLog->record(
            'integration.webhook.deleted',
            'webhook',
            $webhookId,
            $actor,
            ['name' => $name],
        );
    }
}
