<?php

declare(strict_types=1);

namespace App\Modules\Content\Policies;

use App\Models\User;
use App\Modules\Content\Models\Collection;

final class CollectionPolicy
{
    /**
     * Temporary: allow all operations until Auth module is wired.
     */
    public function before(?User $user, string $ability): ?bool
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return false;
    }

    public function view(?User $user, Collection $collection): bool
    {
        return false;
    }

    public function create(?User $user): bool
    {
        return false;
    }

    public function update(?User $user, Collection $collection): bool
    {
        return false;
    }

    public function delete(?User $user, Collection $collection): bool
    {
        return false;
    }
}
