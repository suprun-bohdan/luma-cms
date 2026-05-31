<?php

declare(strict_types=1);

namespace App\Core\System\Policies;

use App\Models\User;
use App\Modules\Users\Services\PermissionEvaluator;

final class SystemUpdatePolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {}

    public function check(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'system.update.check');
    }

    public function run(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'system.update.run');
    }
}
