<?php

declare(strict_types=1);

namespace App\Modules\Setup\Policies;

use App\Models\User;
use App\Modules\Setup\Models\SetupLog;
use App\Modules\Users\Services\PermissionEvaluator;

final class SetupLogPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {}

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'setup.view_logs');
    }

    public function view(?User $user, SetupLog $setupLog): bool
    {
        return $this->permissions->hasPermission($user, 'setup.view_logs');
    }
}
