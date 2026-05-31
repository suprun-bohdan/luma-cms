<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Contracts;

interface PluginContext
{
    public function pluginId(): string;

    public function version(): string;

    /**
     * @param  callable(mixed): void  $listener
     */
    public function listen(string $extensionPoint, callable $listener): void;

    public function registerAdminNavigation(string $label, string $to, int $sortOrder = 100): void;

    /**
     * @param  array<string, mixed>  $defaultProps
     * @param  list<array<string, mixed>>  $fields
     * @param  callable(array<string, mixed>): string  $renderer
     */
    public function registerBlockType(
        string $localType,
        string $label,
        string $description,
        string $category,
        array $defaultProps,
        array $fields,
        callable $renderer,
    ): void;

    /**
     * @param  callable(\Illuminate\Http\Request $request): mixed  $handler
     */
    public function registerRoute(string $method, string $uri, callable $handler): void;

    public function log(string $level, string $message, array $context = []): void;
}
