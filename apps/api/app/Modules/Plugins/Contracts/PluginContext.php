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

    public function log(string $level, string $message, array $context = []): void;
}
