<?php

declare(strict_types=1);

namespace App\Core\Update;

use Illuminate\Support\Facades\Artisan;

class UpdateService
{
    /**
     * @return array{migrations: string}
     */
    public function run(): array
    {
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = trim(Artisan::output());

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return [
            'migrations' => $migrateOutput !== '' ? $migrateOutput : 'Migrations completed.',
        ];
    }
}
