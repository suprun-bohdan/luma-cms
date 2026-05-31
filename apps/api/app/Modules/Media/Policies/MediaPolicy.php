<?php

declare(strict_types=1);

namespace App\Modules\Media\Policies;

use App\Models\User;
use App\Modules\Media\Models\Media;
use App\Modules\Users\Services\PermissionEvaluator;

final class MediaPolicy
{
    public function __construct(
        private readonly PermissionEvaluator $permissions,
    ) {
    }

    public function viewAny(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'media.read');
    }

    public function view(?User $user, Media $media): bool
    {
        return $this->permissions->hasPermission($user, 'media.read');
    }

    public function create(?User $user): bool
    {
        return $this->permissions->hasPermission($user, 'media.upload');
    }

    public function update(?User $user, Media $media): bool
    {
        return $this->permissions->hasPermission($user, 'media.update');
    }

    public function delete(?User $user, Media $media): bool
    {
        return $this->permissions->hasPermission($user, 'media.delete');
    }
}
