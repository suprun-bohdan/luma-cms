<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Policies;

use App\Models\User;
use App\Modules\Integrations\Models\IntegrationToken;
use App\Modules\Users\Services\PermissionEvaluator;

final class IntegrationTokenPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.tokens.manage');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.tokens.manage');
    }

    public function delete(?User $user, IntegrationToken $token): bool
    {
        return $this->permissions->hasPermission($user, 'integrations.tokens.manage');
    }
}
