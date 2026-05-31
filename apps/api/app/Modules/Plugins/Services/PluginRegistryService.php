<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Services\ManifestValidator;
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PluginRegistryService
{
    public function __construct(
        private readonly ManifestValidator $manifestValidator,
    ) {
    }

    /**
     * @return list<array{
     *     plugin_id: string,
     *     name: string,
     *     version: string,
     *     path: string,
     *     manifest: array<string, mixed>,
     *     installed: bool
     * }>
     */
    public function discover(): array
    {
        $installedIds = Plugin::query()->pluck('plugin_id')->all();
        $discovered = [];

        foreach ($this->manifestFiles() as $manifestFile) {
            $manifest = $this->readManifest($manifestFile);
            $this->manifestValidator->validate($manifest);

            if (! $this->manifestValidator->isCompatible($manifest)) {
                continue;
            }

            $pluginPath = $this->relativePluginPath(dirname($manifestFile->getPathname()));
            $pluginId = $manifest['id'];

            $discovered[] = [
                'plugin_id' => $pluginId,
                'name' => (string) $manifest['name'],
                'version' => (string) $manifest['version'],
                'path' => $pluginPath,
                'manifest' => $manifest,
                'installed' => in_array($pluginId, $installedIds, true),
            ];
        }

        usort(
            $discovered,
            static fn (array $left, array $right): int => strcmp($left['plugin_id'], $right['plugin_id']),
        );

        return $discovered;
    }

    /**
     * @return array<string, mixed>
     */
    public function readManifestFromPath(string $relativePath): array
    {
        $manifestPath = $this->absolutePluginPath($relativePath)
            .DIRECTORY_SEPARATOR.config('plugins.manifest_filename', 'luma.plugin.json');

        if (! is_file($manifestPath)) {
            throw new InvalidArgumentException('Plugin manifest not found.');
        }

        return $this->readManifest(new SplFileInfo($manifestPath));
    }

    public function absolutePluginPath(string $relativePath): string
    {
        return rtrim((string) config('plugins.path'), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.ltrim($relativePath, DIRECTORY_SEPARATOR);
    }

    /**
     * @return list<SplFileInfo>
     */
    private function manifestFiles(): array
    {
        $root = (string) config('plugins.path');

        if (! is_dir($root)) {
            return [];
        }

        $filename = (string) config('plugins.manifest_filename', 'luma.plugin.json');
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getFilename() === $filename) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @return array<string, mixed>
     */
    private function readManifest(SplFileInfo $manifestFile): array
    {
        $contents = file_get_contents($manifestFile->getPathname());

        if ($contents === false) {
            throw new InvalidArgumentException('Unable to read plugin manifest.');
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Plugin manifest must be valid JSON.');
        }

        return $decoded;
    }

    private function relativePluginPath(string $absolutePath): string
    {
        $root = rtrim((string) config('plugins.path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return ltrim(str_replace($root, '', $absolutePath), DIRECTORY_SEPARATOR);
    }
}
