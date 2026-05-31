<?php

declare(strict_types=1);

namespace App\Modules\Users\Database\Seeders;

use App\Models\User;
use App\Modules\Users\Models\Permission;
use App\Modules\Users\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View content', 'slug' => 'content.view'],
            ['name' => 'Create content', 'slug' => 'content.create'],
            ['name' => 'Update content', 'slug' => 'content.update'],
            ['name' => 'Delete content', 'slug' => 'content.delete'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name']],
            );
        }

        $admin = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full content access'],
        );

        $editor = Role::query()->updateOrCreate(
            ['slug' => 'editor'],
            ['name' => 'Editor', 'description' => 'Create and edit content'],
        );

        $admin->permissions()->sync(
            Permission::query()->pluck('id'),
        );

        $editor->permissions()->sync(
            Permission::query()
                ->whereIn('slug', ['content.view', 'content.create', 'content.update'])
                ->pluck('id'),
        );

        $email = env('LUMA_SEED_ADMIN_EMAIL', 'admin@luma.test');
        $password = env('LUMA_SEED_ADMIN_PASSWORD', 'password');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Luma Admin',
                'password' => Hash::make($password),
            ],
        );

        $user->roles()->sync([$admin->id]);
    }
}
