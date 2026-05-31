<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

final class ExtensionPointDispatcher
{
    /** @var array<string, array<int, array{plugin_id: string, listener: callable}>> */
    private array $listeners = [];

    public function listen(string $pluginId, string $extensionPoint, callable $listener): void
    {
        $this->listeners[$extensionPoint][] = [
            'plugin_id' => $pluginId,
            'listener' => $listener,
        ];
    }

    public function dispatch(string $extensionPoint, mixed $payload = null): void
    {
        foreach ($this->listeners[$extensionPoint] ?? [] as $registration) {
            try {
                ($registration['listener'])($payload);
            } catch (Throwable $exception) {
                Log::warning('Plugin extension point listener failed', [
                    'extension_point' => $extensionPoint,
                    'plugin_id' => $registration['plugin_id'],
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    public function forgetPlugin(string $pluginId): void
    {
        foreach ($this->listeners as $extensionPoint => $registrations) {
            $this->listeners[$extensionPoint] = array_values(array_filter(
                $registrations,
                static fn (array $registration): bool => $registration['plugin_id'] !== $pluginId,
            ));
        }
    }

    public function clear(): void
    {
        $this->listeners = [];
    }
}
