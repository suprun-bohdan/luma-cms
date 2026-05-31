<?php

declare(strict_types=1);

namespace App\Core\Install;

use InvalidArgumentException;

final class ProductionPasswordGuard
{
    /** @var list<string> */
    private const BLOCKED_PASSWORDS = [
        'password',
        'password123',
        'secret',
        'admin',
        'admin123',
        'change-me',
        'change-me-in-production',
        'changeme',
    ];

    public function assertAllowed(string $password, bool $allowWeak = false): void
    {
        if (trim($password) === '') {
            throw new InvalidArgumentException('Admin password cannot be empty.');
        }

        if ($allowWeak) {
            return;
        }

        if (! $this->isEnforced()) {
            return;
        }

        if (in_array(strtolower($password), self::BLOCKED_PASSWORDS, true)) {
            throw new InvalidArgumentException('This admin password is not allowed in production.');
        }

        if (strlen($password) < 12) {
            throw new InvalidArgumentException('Production admin password must be at least 12 characters.');
        }
    }

    public function isEnforced(): bool
    {
        if (filter_var(config('luma.enforce_strong_passwords'), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        return app()->isProduction();
    }
}
