<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Events;

final readonly class ContentLifecycleEvent
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $entity,
        public int $id,
        public string $slug,
        public string $action,
        public int $actorId,
        public array $meta = [],
    ) {
    }
}
