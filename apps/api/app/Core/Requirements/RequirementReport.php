<?php

declare(strict_types=1);

namespace App\Core\Requirements;

final readonly class RequirementReport
{
    /**
     * @param  list<RequirementCheck>  $checks
     */
    public function __construct(
        public array $checks,
    ) {}

    public function passed(): bool
    {
        foreach ($this->checks as $check) {
            if ($check->isBlocking()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<RequirementCheck>
     */
    public function blockingFailures(): array
    {
        return array_values(array_filter(
            $this->checks,
            static fn (RequirementCheck $check): bool => $check->isBlocking(),
        ));
    }

    /**
     * @return list<array<string, string>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (RequirementCheck $check): array => $check->toArray(),
            $this->checks,
        );
    }
}
