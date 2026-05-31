<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Policies;

use App\Models\User;
use App\Modules\Navigation\Models\Menu;
use App\Modules\Users\Services\PermissionEvaluator;

final class MenuPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'menus.view');
    }

    public function view(?User $user, Menu $menu): bool
    {
        return $this->permissions->hasPermission($user, 'menus.view');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'menus.create');
    }

    public function update(?User $user, Menu $menu): bool
    {
        return $this->permissions->hasPermission($user, 'menus.update');
    }

    public function delete(?User $user, Menu $menu): bool
    {
        return $this->permissions->hasPermission($user, 'menus.delete');
    }
}
