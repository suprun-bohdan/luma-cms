<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Contracts\PluginContract;
use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use RuntimeException;

final class PluginRuntimeService
{
    /** @var array<string, PluginContract> */
    private array $loaded = [];

    public function __construct(
        private readonly PluginRegistryService $registry,
        private readonly ExtensionPointDispatcher $dispatcher,
        private readonly AdminNavigationRegistry $adminNavigation,
        private readonly BlockTypeRegistry $blockTypes,
        private readonly PluginRouteRegistry $pluginRoutes,
        private readonly CapabilityGate $capabilityGate,
    ) {
    }

    public function bootEnabledPlugins(): void
    {
        Plugin::query()
            ->where('status', PluginStatus::Enabled)
            ->orderBy('plugin_id')
            ->each(function (Plugin $plugin): void {
                $this->registerPlugin($plugin);
            });

        $this->dispatcher->dispatch('system.booted');
    }

    public function registerPlugin(Plugin $plugin): void
    {
        if (isset($this->loaded[$plugin->plugin_id])) {
            return;
        }

        $plugin->loadMissing('capabilities');

        $instance = $this->instantiate($plugin);
        $context = new DefaultPluginContext(
            $plugin,
            $this->dispatcher,
            $this->adminNavigation,
            $this->blockTypes,
            $this->pluginRoutes,
            $this->capabilityGate,
        );

        $instance->register($context);
        $instance->boot($context);

        $this->loaded[$plugin->plugin_id] = $instance;
    }

    public function unregisterPlugin(Plugin $plugin): void
    {
        unset($this->loaded[$plugin->plugin_id]);
        $this->dispatcher->forgetPlugin($plugin->plugin_id);
        $this->adminNavigation->forgetPlugin($plugin->plugin_id);
        $this->blockTypes->forgetPlugin($plugin->plugin_id);
        $this->pluginRoutes->forgetPlugin($plugin->plugin_id);
    }

    public function resetRuntimeState(): void
    {
        $this->loaded = [];
    }

    private function instantiate(Plugin $plugin): PluginContract
    {
        $backendEntry = $plugin->manifest['entrypoints']['backend'] ?? null;

        if (! is_string($backendEntry) || $backendEntry === '') {
            throw new RuntimeException('Plugin backend entrypoint is missing.');
        }

        $absolutePath = $this->registry->absolutePluginPath($plugin->path)
            .DIRECTORY_SEPARATOR.ltrim($backendEntry, DIRECTORY_SEPARATOR);

        if (! is_file($absolutePath)) {
            throw new RuntimeException('Plugin backend entrypoint file not found.');
        }

        require_once $absolutePath;

        $className = $this->resolvePluginClass($absolutePath);

        if ($className === null) {
            throw new RuntimeException('Plugin backend class not found.');
        }

        $instance = new $className();

        if (! $instance instanceof PluginContract) {
            throw new RuntimeException('Plugin backend must implement PluginContract.');
        }

        return $instance;
    }

    private function resolvePluginClass(string $absolutePath): ?string
    {
        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            return null;
        }

        $namespace = null;
        $class = null;

        if (preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatch) === 1) {
            $namespace = $namespaceMatch[1];
        }

        if (preg_match('/class\s+(\w+)/', $contents, $classMatch) === 1) {
            $class = $classMatch[1];
        }

        if ($class === null) {
            return null;
        }

        return $namespace !== null ? $namespace.'\\'.$class : $class;
    }
}
