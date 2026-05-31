<?php

declare(strict_types=1);

namespace App\Modules\Forms\Policies;

use App\Models\User;
use App\Modules\Forms\Models\Form;
use App\Modules\Users\Services\PermissionEvaluator;

final class FormPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'forms.manage')
            || $this->permissions->hasPermission($user, 'forms.read_submissions');
    }

    public function view(?User $user, Form $form): bool
    {
        return $this->permissions->hasPermission($user, 'forms.manage')
            || $this->permissions->hasPermission($user, 'forms.read_submissions');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'forms.manage');
    }

    public function update(?User $user, Form $form): bool
    {
        return $this->permissions->hasPermission($user, 'forms.manage');
    }

    public function delete(?User $user, Form $form): bool
    {
        return $this->permissions->hasPermission($user, 'forms.manage');
    }

    public function viewSubmissions(?User $user, Form $form): bool
    {
        return $this->permissions->hasPermission($user, 'forms.read_submissions');
    }
}
