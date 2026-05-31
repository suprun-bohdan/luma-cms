<?php

declare(strict_types=1);

namespace App\Modules\Content\Policies;

use App\Models\User;
use App\Modules\Content\Models\Collection;
use App\Modules\Users\Services\PermissionEvaluator;

final class CollectionPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'content.view');
    }

    public function view(?User $user, Collection $collection): bool
    {
        return $this->permissions->hasPermission($user, 'content.view');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'content.create');
    }

    public function update(?User $user, Collection $collection): bool
    {
        return $this->permissions->hasPermission($user, 'content.update');
    }

    public function delete(?User $user, Collection $collection): bool
    {
        return $this->permissions->hasPermission($user, 'content.delete');
    }
}
