<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Support\DangerousCapabilities;
use InvalidArgumentException;

final class ManifestValidator
{
    /** @var list<string> */
    private const REQUIRED_FIELDS = [
        'schemaVersion',
        'id',
        'name',
        'description',
        'version',
        'type',
        'author',
        'compatibility',
        'capabilities',
        'extensionPoints',
        'entrypoints',
        'migrations',
    ];

    /** @var list<string> */
    private const ALLOWED_EXTENSION_POINTS = [
        'system.booted',
        'content.afterCreate',
        'content.afterUpdate',
        'content.afterPublish',
        'admin.navigation',
        'render.block',
    ];

    /**
     * @return array<string, mixed>
     */
    public function validate(array $manifest): array
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $manifest)) {
                throw new InvalidArgumentException("Manifest missing required field: {$field}");
            }
        }

        if (($manifest['type'] ?? null) !== 'plugin') {
            throw new InvalidArgumentException('Manifest type must be plugin.');
        }

        if (! is_string($manifest['id']) || $manifest['id'] === '') {
            throw new InvalidArgumentException('Manifest id must be a non-empty string.');
        }

        if (! is_array($manifest['capabilities'])) {
            throw new InvalidArgumentException('Manifest capabilities must be an array.');
        }

        if (! is_array($manifest['extensionPoints'])) {
            throw new InvalidArgumentException('Manifest extensionPoints must be an array.');
        }

        foreach ($manifest['extensionPoints'] as $extensionPoint) {
            if (! is_string($extensionPoint) || ! in_array($extensionPoint, self::ALLOWED_EXTENSION_POINTS, true)) {
                throw new InvalidArgumentException('Manifest contains unsupported extension point.');
            }
        }

        if (! is_array($manifest['entrypoints']) || ! is_string($manifest['entrypoints']['backend'] ?? null)) {
            throw new InvalidArgumentException('Manifest entrypoints.backend is required.');
        }

        return $manifest;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return list<string>
     */
    public function declaredCapabilities(array $manifest): array
    {
        $capabilities = $manifest['capabilities'] ?? [];

        if (! is_array($capabilities)) {
            return [];
        }

        return array_values(array_filter(
            $capabilities,
            static fn (mixed $capability): bool => is_string($capability) && $capability !== '',
        ));
    }

    public function isCompatible(array $manifest): bool
    {
        $lumaVersion = config('plugins.luma_version', '0.1.0');
        $constraint = $manifest['compatibility']['luma'] ?? null;

        if (! is_string($constraint)) {
            return false;
        }

        return $this->matchesCaretConstraint($lumaVersion, $constraint);
    }

    private function matchesCaretConstraint(string $current, string $constraint): bool
    {
        if (! str_starts_with($constraint, '^')) {
            return $constraint === $current;
        }

        $minimum = substr($constraint, 1);

        return version_compare($current, $minimum, '>=');
    }
}
