<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Support;

final class DangerousCapabilities
{
    /** @var list<string> */
    public const ALL = [
        'system.settings.update',
        'database.migrate',
        'routes.register',
        'ai.read_context',
        'content.bulk_update',
        'content.delete',
        'users.read',
        'users.update',
        'secrets.read',
    ];

    public static function isDangerous(string $capability): bool
    {
        return in_array($capability, self::ALL, true);
    }
}
