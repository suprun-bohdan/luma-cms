<?php

declare(strict_types=1);

namespace App\Core\Requirements;

final readonly class RequirementCheck
{
    public function __construct(
        public string $id,
        public string $label,
        public RequirementStatus $status,
        public string $message,
    ) {}

    public function isBlocking(): bool
    {
        return $this->status === RequirementStatus::Failed;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'status' => $this->status->value,
            'message' => $this->message,
        ];
    }
}
