<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Models\Plugin;
use Illuminate\Support\Facades\Log;

final class DefaultPluginContext implements PluginContext
{
    public function __construct(
        private readonly Plugin $plugin,
        private readonly ExtensionPointDispatcher $dispatcher,
        private readonly AdminNavigationRegistry $adminNavigation,
    ) {
    }

    public function pluginId(): string
    {
        return $this->plugin->plugin_id;
    }

    public function version(): string
    {
        return $this->plugin->version;
    }

    public function listen(string $extensionPoint, callable $listener): void
    {
        $this->dispatcher->listen($this->plugin->plugin_id, $extensionPoint, $listener);
    }

    public function registerAdminNavigation(string $label, string $to, int $sortOrder = 100): void
    {
        $this->adminNavigation->register($this->plugin->plugin_id, $label, $to, $sortOrder);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, sprintf('[plugin:%s] %s', $this->plugin->plugin_id, $message), $context);
    }
}
