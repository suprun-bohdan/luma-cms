<?php

declare(strict_types=1);

namespace App\Core\Update;

use App\Modules\Setup\Services\InstallationStateService;
use Illuminate\Support\Facades\File;

final class SystemVersionService
{
    public function __construct(
        private readonly InstallationStateService $installationState,
    ) {}

    /**
     * @return array{
     *     version: string,
     *     flavor: string|null,
     *     build: string|null,
     *     installed: bool,
     *     manifest_path: string|null
     * }
     */
    public function current(): array
    {
        $manifest = $this->readManifest();
        $manifestPath = $this->manifestPath();

        return [
            'version' => (string) config('luma.version', '0.0.0-dev'),
            'flavor' => isset($manifest['flavor']) ? (string) $manifest['flavor'] : null,
            'build' => isset($manifest['build']) ? (string) $manifest['build'] : null,
            'installed' => $this->installationState->isInstalled(),
            'manifest_path' => $manifestPath !== null && File::exists($manifestPath) ? 'luma-manifest.json' : null,
        ];
    }

    /**
     * @return array{
     *     update_available: bool,
     *     current: string,
     *     latest: string,
     *     channel: string
     * }
     */
    public function checkForUpdates(): array
    {
        $current = (string) config('luma.version', '0.0.0-dev');
        $manifest = $this->readManifest();
        $latest = isset($manifest['version']) ? (string) $manifest['version'] : $current;

        return [
            'update_available' => version_compare($latest, $current, '>'),
            'current' => $current,
            'latest' => $latest,
            'channel' => 'manual',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readManifest(): array
    {
        $path = $this->manifestPath();

        if ($path === null || ! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function manifestPath(): ?string
    {
        $path = dirname(base_path(), 2).'/luma-manifest.json';

        return $path;
    }
}
