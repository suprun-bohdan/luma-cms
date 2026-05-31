<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Models\User;
use App\Modules\Plugins\Contracts\PluginContract;
use App\Modules\Plugins\Enums\PluginStatus;
use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Support\DangerousCapabilities;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class PluginLifecycleService
{
    public function __construct(
        private readonly PluginRegistryService $registry,
        private readonly ManifestValidator $manifestValidator,
        private readonly AuditLogService $auditLog,
        private readonly CapabilityGate $capabilityGate,
        private readonly PluginRuntimeService $runtime,
        private readonly ExtensionPointDispatcher $dispatcher,
    ) {
    }

    public function install(string $pluginId, ?User $actor = null): Plugin
    {
        $candidate = collect($this->registry->discover())
            ->firstWhere('plugin_id', $pluginId);

        if ($candidate === null) {
            throw new InvalidArgumentException('Plugin not found on disk.');
        }

        if ($candidate['installed']) {
            throw new InvalidArgumentException('Plugin is already installed.');
        }

        return DB::transaction(function () use ($candidate, $actor): Plugin {
            $plugin = Plugin::query()->create([
                'plugin_id' => $candidate['plugin_id'],
                'name' => $candidate['name'],
                'version' => $candidate['version'],
                'status' => PluginStatus::Installed,
                'manifest' => $candidate['manifest'],
                'path' => $candidate['path'],
                'installed_at' => now(),
            ]);

            $this->syncCapabilities($plugin);

            $this->auditLog->record(
                'plugin.installed',
                'plugin',
                $plugin->plugin_id,
                $actor,
                ['version' => $plugin->version],
            );

            return $plugin->load('capabilities');
        });
    }

    public function enable(Plugin $plugin, ?User $actor = null): Plugin
    {
        if ($plugin->status === PluginStatus::Enabled) {
            return $plugin->load('capabilities');
        }

        if ($this->capabilityGate->pendingDangerousCapabilities($plugin) > 0) {
            throw new RuntimeException('Dangerous capabilities require approval before enable.');
        }

        return DB::transaction(function () use ($plugin, $actor): Plugin {
            $this->runtime->registerPlugin($plugin);

            $plugin->update([
                'status' => PluginStatus::Enabled,
                'enabled_at' => now(),
            ]);

            $this->auditLog->record(
                'plugin.enabled',
                'plugin',
                $plugin->plugin_id,
                $actor,
            );

            return $plugin->fresh('capabilities');
        });
    }

    public function disable(Plugin $plugin, ?User $actor = null): Plugin
    {
        if ($plugin->status !== PluginStatus::Enabled) {
            $plugin->update(['status' => PluginStatus::Disabled]);

            return $plugin->load('capabilities');
        }

        $this->runtime->unregisterPlugin($plugin);

        $plugin->update([
            'status' => PluginStatus::Disabled,
            'enabled_at' => null,
        ]);

        $this->auditLog->record(
            'plugin.disabled',
            'plugin',
            $plugin->plugin_id,
            $actor,
        );

        return $plugin->fresh('capabilities');
    }

    public function uninstall(Plugin $plugin, ?User $actor = null): void
    {
        if ($plugin->status === PluginStatus::Enabled) {
            $this->disable($plugin, $actor);
        }

        DB::transaction(function () use ($plugin, $actor): void {
            $pluginId = $plugin->plugin_id;
            $plugin->delete();

            $this->auditLog->record(
                'plugin.uninstalled',
                'plugin',
                $pluginId,
                $actor,
            );
        });
    }

    public function approveCapability(Plugin $plugin, string $capability, User $actor): Plugin
    {
        if (! DangerousCapabilities::isDangerous($capability)) {
            throw new InvalidArgumentException('Only dangerous capabilities require approval.');
        }

        $record = $plugin->capabilities()->where('capability', $capability)->first();

        if ($record === null) {
            throw new InvalidArgumentException('Capability is not declared by this plugin.');
        }

        $record->update([
            'granted' => true,
            'approved_at' => now(),
            'approved_by' => $actor->id,
        ]);

        $this->auditLog->record(
            'plugin.capability.approved',
            'plugin',
            $plugin->plugin_id,
            $actor,
            ['capability' => $capability],
        );

        return $plugin->fresh('capabilities');
    }

    private function syncCapabilities(Plugin $plugin): void
    {
        $declared = $this->manifestValidator->declaredCapabilities($plugin->manifest);

        foreach ($declared as $capability) {
            $plugin->capabilities()->updateOrCreate(
                ['capability' => $capability],
                [
                    'granted' => ! DangerousCapabilities::isDangerous($capability),
                    'approved_at' => DangerousCapabilities::isDangerous($capability) ? null : now(),
                    'approved_by' => null,
                ],
            );
        }
    }
}
