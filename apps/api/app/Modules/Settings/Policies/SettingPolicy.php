<?php

declare(strict_types=1);

namespace App\Modules\Settings\Policies;

use App\Models\User;
use App\Modules\Settings\Models\Setting;
use App\Modules\Users\Services\PermissionEvaluator;

final class SettingPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {}

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'settings.manage');
    }

    public function update(?User $user, Setting $setting): bool
    {
        return $this->permissions->hasPermission($user, 'settings.manage');
    }
}
