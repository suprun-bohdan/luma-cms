<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Install;

use App\Core\Install\ProductionPasswordGuard;
use InvalidArgumentException;
use Tests\TestCase;

final class ProductionPasswordGuardTest extends TestCase
{
    public function test_allows_weak_password_in_testing_environment(): void
    {
        $guard = app(ProductionPasswordGuard::class);

        $guard->assertAllowed('password');

        $this->assertFalse($guard->isEnforced());
    }

    public function test_rejects_empty_password(): void
    {
        $guard = app(ProductionPasswordGuard::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');

        $guard->assertAllowed('   ');
    }

    public function test_rejects_weak_password_when_enforced(): void
    {
        config(['luma.enforce_strong_passwords' => true]);

        $guard = app(ProductionPasswordGuard::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not allowed');

        $guard->assertAllowed('password');
    }

    public function test_rejects_default_admin_password_when_enforced(): void
    {
        config(['luma.enforce_strong_passwords' => true]);

        $guard = app(ProductionPasswordGuard::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not allowed');

        $guard->assertAllowed('admin');
    }

    public function test_rejects_short_password_when_enforced(): void
    {
        config(['luma.enforce_strong_passwords' => true]);

        $guard = app(ProductionPasswordGuard::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 12 characters');

        $guard->assertAllowed('abcdefghijk');
    }
}