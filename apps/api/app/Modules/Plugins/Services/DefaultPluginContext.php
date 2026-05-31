<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Support\BlockFieldDefinition;
use App\Modules\Plugins\Support\BlockTypeDefinition;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

final class DefaultPluginContext implements PluginContext
{
    public function __construct(
        private readonly Plugin $plugin,
        private readonly ExtensionPointDispatcher $dispatcher,
        private readonly AdminNavigationRegistry $adminNavigation,
        private readonly BlockTypeRegistry $blockTypes,
        private readonly PluginRouteRegistry $pluginRoutes,
        private readonly CapabilityGate $capabilityGate,
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

    public function registerBlockType(
        string $localType,
        string $label,
        string $description,
        string $category,
        array $defaultProps,
        array $fields,
        callable $renderer,
    ): void {
        $this->assertExtensionPoint('render.block');
        $this->assertCapability('editor.extend');

        if (! preg_match('/^[a-z0-9][a-z0-9_-]*$/', $localType)) {
            throw new InvalidArgumentException('Block local type must be lowercase alphanumeric with _ or -.');
        }

        $fieldDefinitions = array_map(
            static fn (array $field): BlockFieldDefinition => BlockFieldDefinition::fromArray($field),
            $fields,
        );

        $this->blockTypes->register(
            new BlockTypeDefinition(
                type: $this->plugin->plugin_id.'/'.$localType,
                label: $label,
                description: $description,
                category: $category,
                defaultProps: $defaultProps,
                fields: $fieldDefinitions,
                pluginId: $this->plugin->plugin_id,
                source: 'plugin',
            ),
            $renderer,
        );
    }

    public function registerRoute(string $method, string $uri, callable $handler): void
    {
        $this->assertCapability('routes.register');

        $this->pluginRoutes->register($this->plugin->plugin_id, $method, $uri, $handler);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, sprintf('[plugin:%s] %s', $this->plugin->plugin_id, $message), $context);
    }

    private function assertExtensionPoint(string $extensionPoint): void
    {
        $declaredPoints = $this->plugin->manifest['extensionPoints'] ?? [];

        if (! is_array($declaredPoints) || ! in_array($extensionPoint, $declaredPoints, true)) {
            throw new RuntimeException(sprintf('Extension point %s is not declared in plugin manifest.', $extensionPoint));
        }
    }

    private function assertCapability(string $capability): void
    {
        if (! $this->capabilityGate->allows($this->plugin, $capability)) {
            throw new RuntimeException(sprintf('Capability %s is not granted for this plugin.', $capability));
        }
    }
}
