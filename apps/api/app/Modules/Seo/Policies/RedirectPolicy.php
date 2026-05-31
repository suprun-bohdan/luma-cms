<?php

declare(strict_types=1);

namespace App\Modules\Seo\Policies;

use App\Models\User;
use App\Modules\Seo\Models\Redirect;
use App\Modules\Users\Services\PermissionEvaluator;

final class RedirectPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'seo.manage');
    }

    public function view(?User $user, Redirect $redirect): bool
    {
        return $this->permissions->hasPermission($user, 'seo.manage');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'seo.manage');
    }

    public function update(?User $user, Redirect $redirect): bool
    {
        return $this->permissions->hasPermission($user, 'seo.manage');
    }

    public function delete(?User $user, Redirect $redirect): bool
    {
        return $this->permissions->hasPermission($user, 'seo.manage');
    }
}
