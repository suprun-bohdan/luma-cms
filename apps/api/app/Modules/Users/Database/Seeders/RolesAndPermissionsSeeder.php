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
            ['name' => 'View media', 'slug' => 'media.read'],
            ['name' => 'Upload media', 'slug' => 'media.upload'],
            ['name' => 'Update media', 'slug' => 'media.update'],
            ['name' => 'Delete media', 'slug' => 'media.delete'],
            ['name' => 'View pages', 'slug' => 'pages.view'],
            ['name' => 'Create pages', 'slug' => 'pages.create'],
            ['name' => 'Update pages', 'slug' => 'pages.update'],
            ['name' => 'Delete pages', 'slug' => 'pages.delete'],
            ['name' => 'Publish pages', 'slug' => 'pages.publish'],
            ['name' => 'View menus', 'slug' => 'menus.view'],
            ['name' => 'Create menus', 'slug' => 'menus.create'],
            ['name' => 'Update menus', 'slug' => 'menus.update'],
            ['name' => 'Delete menus', 'slug' => 'menus.delete'],
            ['name' => 'Manage SEO', 'slug' => 'seo.manage'],
            ['name' => 'Manage forms', 'slug' => 'forms.manage'],
            ['name' => 'Read form submissions', 'slug' => 'forms.read_submissions'],
            ['name' => 'Manage plugins', 'slug' => 'plugins.manage'],
            ['name' => 'View plugin audit logs', 'slug' => 'plugins.audit'],
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
                ->whereIn('slug', [
                    'content.view',
                    'content.create',
                    'content.update',
                    'media.read',
                    'media.upload',
                    'media.update',
                    'pages.view',
                    'pages.create',
                    'pages.update',
                    'pages.publish',
                    'menus.view',
                    'menus.update',
                ])
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
