<?php

declare(strict_types=1);

namespace App\Modules\Content\Policies;

use App\Models\User;
use App\Modules\Content\Models\Entry;
use App\Modules\Users\Services\PermissionEvaluator;

final class EntryPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'content.view');
    }

    public function view(?User $user, Entry $entry): bool
    {
        return $this->permissions->hasPermission($user, 'content.view');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'content.create');
    }

    public function update(?User $user, Entry $entry): bool
    {
        return $this->permissions->hasPermission($user, 'content.update');
    }

    public function delete(?User $user, Entry $entry): bool
    {
        return $this->permissions->hasPermission($user, 'content.delete');
    }
}
