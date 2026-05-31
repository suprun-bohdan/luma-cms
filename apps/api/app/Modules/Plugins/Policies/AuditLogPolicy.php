<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Policies;

use App\Models\User;
use App\Modules\Plugins\Models\AuditLog;
use App\Modules\Users\Services\PermissionEvaluator;

final class AuditLogPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.audit');
    }
}
