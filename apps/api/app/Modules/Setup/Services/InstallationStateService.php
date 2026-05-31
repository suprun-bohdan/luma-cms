<?php

declare(strict_types=1);

namespace App\Modules\Setup\Services;

use App\Modules\Setup\Models\LumaInstallation;
use Illuminate\Support\Facades\Schema;

final class InstallationStateService
{
    public function projectRootPath(): string
    {
        return dirname(base_path(), 2);
    }

    public function installedMarkerPath(): string
    {
        return storage_path('app/.luma-installed');
    }

    public function installScriptPath(): string
    {
        return $this->projectRootPath().'/install.php';
    }

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

        file_put_contents(
            $this->installedMarkerPath(),
            json_encode(['version' => $version, 'installed_at' => now()->toIso8601String()], JSON_THROW_ON_ERROR),
        );

        $this->removeInstallScript();
    }

    private function removeInstallScript(): void
    {
        $path = $this->installScriptPath();

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
