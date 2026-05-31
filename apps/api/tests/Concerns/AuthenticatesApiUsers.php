<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\User;
use App\Modules\Users\Database\Seeders\RolesAndPermissionsSeeder;
use App\Modules\Users\Models\Role;
use Illuminate\Support\Facades\Hash;

trait AuthenticatesApiUsers
{
    protected function seedRbac(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function adminUser(): User
    {
        return User::query()->where('email', 'admin@luma.test')->firstOrFail();
    }

    protected function editorUser(): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'editor@luma.test'],
            [
                'name' => 'Luma Editor',
                'password' => Hash::make('password'),
            ],
        );

        $editorRole = Role::query()->where('slug', 'editor')->firstOrFail();
        $user->roles()->sync([$editorRole->id]);

        return $user->fresh('roles');
    }

    protected function ownerUser(): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'owner@luma.test'],
            [
                'name' => 'Luma Owner',
                'password' => Hash::make('password'),
            ],
        );

        $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
        $user->roles()->sync([$ownerRole->id]);

        return $user->fresh('roles');
    }

    protected function bearerToken(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function withBearer(User $user): array
    {
        return [
            'Authorization' => 'Bearer '.$this->bearerToken($user),
        ];
    }
}
