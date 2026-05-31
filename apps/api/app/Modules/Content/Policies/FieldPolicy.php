<?php

declare(strict_types=1);

namespace App\Modules\Content\Policies;

use App\Models\User;
use App\Modules\Content\Models\Field;
use App\Modules\Users\Services\PermissionEvaluator;

final class FieldPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'content.view');
    }

    public function view(?User $user, Field $field): bool
    {
        return $this->permissions->hasPermission($user, 'content.view');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'content.create');
    }

    public function update(?User $user, Field $field): bool
    {
        return $this->permissions->hasPermission($user, 'content.update');
    }

    public function delete(?User $user, Field $field): bool
    {
        return $this->permissions->hasPermission($user, 'content.delete');
    }
}
