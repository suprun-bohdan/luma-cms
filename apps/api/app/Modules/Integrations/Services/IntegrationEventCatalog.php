<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

use App\Modules\Integrations\Enums\IntegrationEvent;
use InvalidArgumentException;

final class IntegrationEventCatalog
{
    /** @return list<string> */
    public function all(): array
    {
        return IntegrationEvent::all();
    }

    public function assertValid(string $event): void
    {
        if (! in_array($event, $this->all(), true)) {
            throw new InvalidArgumentException("Unknown integration event [{$event}].");
        }
    }

    /** @param list<string> $events */
    public function assertValidMany(array $events): void
    {
        foreach ($events as $event) {
            if (! is_string($event)) {
                throw new InvalidArgumentException('Integration events must be strings.');
            }

            $this->assertValid($event);
        }
    }
}
