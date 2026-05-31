<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Services;

final class IntegrationScopeCatalog
{
    public const ContentRead = 'content:read';

    public const FormsReadSubmissions = 'forms:read_submissions';

    public const MediaRead = 'media:read';

    /** @return list<string> */
    public function all(): array
    {
        return [
            self::ContentRead,
            self::FormsReadSubmissions,
            self::MediaRead,
        ];
    }

    /** @param list<string> $abilities */
    public function assertValidMany(array $abilities): void
    {
        foreach ($abilities as $ability) {
            if (! is_string($ability) || ! in_array($ability, $this->all(), true)) {
                throw new \InvalidArgumentException('Invalid integration token ability.');
            }
        }
    }

    public function middlewareKey(string $ability): string
    {
        return match ($ability) {
            self::ContentRead => 'content_read',
            self::FormsReadSubmissions => 'forms_read_submissions',
            self::MediaRead => 'media_read',
            default => throw new \InvalidArgumentException("Unknown ability [{$ability}]."),
        };
    }

    public function abilityFromMiddlewareKey(string $key): string
    {
        return match ($key) {
            'content_read' => self::ContentRead,
            'forms_read_submissions' => self::FormsReadSubmissions,
            'media_read' => self::MediaRead,
            default => $key,
        };
    }
}
