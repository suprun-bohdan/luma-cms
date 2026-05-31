<?php

declare(strict_types=1);

namespace App\Modules\Pages\Policies;

use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Modules\Users\Services\PermissionEvaluator;

final class PagePolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'pages.view');
    }

    public function view(?User $user, Page $page): bool
    {
        return $this->permissions->hasPermission($user, 'pages.view');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'pages.create');
    }

    public function update(?User $user, Page $page): bool
    {
        return $this->permissions->hasPermission($user, 'pages.update');
    }

    public function delete(?User $user, Page $page): bool
    {
        return $this->permissions->hasPermission($user, 'pages.delete');
    }

    public function publish(?User $user, Page $page): bool
    {
        return $this->permissions->hasPermission($user, 'pages.publish');
    }
}
