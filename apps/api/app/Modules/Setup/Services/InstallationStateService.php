<?php

declare(strict_types=1);

namespace App\Modules\Setup\Services;

use App\Modules\Setup\Models\LumaInstallation;
use Illuminate\Support\Facades\Schema;

final class InstallationStateService
{
    public function isInstalled(): bool
    {
        if (! Schema::hasTable('luma_installation')) {
            return false;
        }

        return LumaInstallation::query()
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function markInstalled(string $version): void
    {
        LumaInstallation::query()->updateOrCreate(
            ['id' => 1],
            [
                'completed_at' => now(),
                'version' => $version,
            ],
        );
    }
}
