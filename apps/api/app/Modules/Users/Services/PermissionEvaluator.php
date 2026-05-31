<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Models\User;

final class PermissionEvaluator
{
    public function hasPermission(?User $user, string $permissionSlug): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->roles()
            ->whereHas('permissions', fn ($query) => $query->where('slug', $permissionSlug))
            ->exists();
    }
}
