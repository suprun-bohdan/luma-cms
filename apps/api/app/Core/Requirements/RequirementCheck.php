<?php

declare(strict_types=1);

namespace App\Core\Requirements;

final readonly class RequirementCheck
{
    /**
     * @param  array<string, string|int|float>  $messageParams
     */
    public function __construct(
        public string $id,
        public string $label,
        public RequirementStatus $status,
        public string $message,
        public ?string $labelKey = null,
        public ?string $messageKey = null,
        public array $messageParams = [],
    ) {}

    public function isBlocking(): bool
    {
        return $this->status === RequirementStatus::Failed;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'id' => $this->id,
            'label' => $this->label,
            'status' => $this->status->value,
            'message' => $this->message,
            'label_key' => $this->labelKey ?? self::labelKeyFor($this->id),
            'message_key' => $this->messageKey,
            'message_params' => $this->messageParams === [] ? new \stdClass() : $this->messageParams,
        ];

        return $payload;
    }

    public static function labelKeyFor(string $id): string
    {
        return 'requirements.'.str_replace('.', '_', $id).'.label';
    }

    public static function messageKeyFor(string $id, string $variant): string
    {
        return 'requirements.'.str_replace('.', '_', $id).'.'.$variant;
    }
}
