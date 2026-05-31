<?php

namespace Database\Seeders;

use App\Modules\Users\Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
    }
}
