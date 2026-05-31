<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Contracts\PluginContract;
use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Loads trusted server-side PHP plugin entrypoints.
 *
 * Luma plugins are not sandboxed PHP. Only install plugins from trusted sources.
 * Entrypoint paths are validated with realpath() and must stay inside the plugin directory.
 */
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
        private readonly AuditLogService $auditLog,
    ) {
    }

    public function bootEnabledPlugins(): void
    {
        Plugin::query()
            ->where('status', PluginStatus::Enabled)
            ->orderBy('plugin_id')
            ->each(function (Plugin $plugin): void {
                try {
                    $this->registerPlugin($plugin);
                } catch (Throwable $exception) {
                    $this->markPluginBootFailed($plugin, $exception);
                }
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

    private function markPluginBootFailed(Plugin $plugin, Throwable $exception): void
    {
        $message = substr($exception->getMessage(), 0, 1000);

        $this->unregisterPlugin($plugin);

        $plugin->update([
            'status' => PluginStatus::Failed,
            'last_error' => $message,
            'enabled_at' => null,
        ]);

        $this->auditLog->record(
            'plugin.boot_failed',
            'plugin',
            $plugin->plugin_id,
            null,
            ['message' => $message],
        );

        Log::warning('Plugin boot failed', [
            'plugin_id' => $plugin->plugin_id,
            'message' => $message,
        ]);
    }

    private function instantiate(Plugin $plugin): PluginContract
    {
        $backendEntry = $plugin->manifest['entrypoints']['backend'] ?? null;

        if (! is_string($backendEntry) || $backendEntry === '') {
            throw new RuntimeException('Plugin backend entrypoint is missing.');
        }

        $pluginRoot = realpath($this->registry->absolutePluginPath($plugin->path));

        if ($pluginRoot === false) {
            throw new RuntimeException('Plugin directory not found.');
        }

        $absolutePath = $pluginRoot.DIRECTORY_SEPARATOR.ltrim($backendEntry, DIRECTORY_SEPARATOR);
        $entry = realpath($absolutePath);

        if ($entry === false || ! is_file($entry) || ! str_starts_with($entry, $this->normalizedPluginRoot($pluginRoot))) {
            throw new RuntimeException('Plugin entrypoint escapes plugin directory.');
        }

        // Plugins are trusted PHP extensions loaded via require_once after path validation.
        require_once $entry;

        $className = $this->resolvePluginClass($entry);

        if ($className === null) {
            throw new RuntimeException('Plugin backend class not found.');
        }

        $instance = new $className();

        if (! $instance instanceof PluginContract) {
            throw new RuntimeException('Plugin backend must implement PluginContract.');
        }

        return $instance;
    }

    private function normalizedPluginRoot(string $pluginRoot): string
    {
        return rtrim($pluginRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    private function resolvePluginClass(string $absolutePath): ?string
    {
        $contents = File::get($absolutePath);

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
