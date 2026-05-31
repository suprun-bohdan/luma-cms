<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

final class IntegrationEvent
{
    public const PagePublished = 'page.published';

    public const EntryPublished = 'entry.published';

    public const FormSubmissionCreated = 'form.submission.created';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::PagePublished,
            self::EntryPublished,
            self::FormSubmissionCreated,
        ];
    }
}
