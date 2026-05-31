<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

final readonly class LoginResult
{
    public function __construct(
        public string $token,
        public \App\Models\User $user,
    ) {
    }
}
