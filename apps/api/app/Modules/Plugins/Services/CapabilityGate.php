<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Modules\Plugins\Models\Plugin;
use App\Modules\Plugins\Support\DangerousCapabilities;

final class CapabilityGate
{
    public function allows(Plugin $plugin, string $capability): bool
    {
        if (! in_array($capability, $this->declaredCapabilities($plugin), true)) {
            return false;
        }

        $record = $plugin->capabilities()
            ->where('capability', $capability)
            ->first();

        return $record !== null && $record->granted;
    }

    public function pendingDangerousCapabilities(Plugin $plugin): int
    {
        return $plugin->capabilities()
            ->where('granted', false)
            ->whereIn('capability', DangerousCapabilities::ALL)
            ->count();
    }

    /**
     * @return list<string>
     */
    private function declaredCapabilities(Plugin $plugin): array
    {
        $manifestCapabilities = $plugin->manifest['capabilities'] ?? [];

        if (! is_array($manifestCapabilities)) {
            return [];
        }

        return array_values(array_filter(
            $manifestCapabilities,
            static fn (mixed $capability): bool => is_string($capability) && $capability !== '',
        ));
    }
}
