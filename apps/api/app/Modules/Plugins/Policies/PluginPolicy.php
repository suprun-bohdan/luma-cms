<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Policies;

use App\Models\User;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Users\Services\PermissionEvaluator;

final class PluginPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }

    public function view(?User $user, Plugin $plugin): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }

    public function install(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }

    public function enable(?User $user, Plugin $plugin): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }

    public function disable(?User $user, Plugin $plugin): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }

    public function delete(?User $user, Plugin $plugin): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }

    public function approveCapability(?User $user, Plugin $plugin): bool
    {
        return $this->permissions->hasPermission($user, 'plugins.manage');
    }
}
