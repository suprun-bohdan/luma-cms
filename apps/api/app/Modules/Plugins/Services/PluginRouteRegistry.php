<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use InvalidArgumentException;

final class PluginRouteRegistry
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function register(string $pluginId, string $method, string $uri, callable $handler): void
    {
        $normalizedMethod = strtoupper(trim($method));
        $normalizedUri = trim($uri, '/');

        if ($normalizedMethod === '' || ! in_array($normalizedMethod, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
            throw new InvalidArgumentException('Plugin route method must be GET, POST, PUT, or DELETE.');
        }

        $this->routes[$this->key($pluginId, $normalizedMethod, $normalizedUri)] = $handler;
    }

    public function match(string $pluginId, string $method, string $uri): ?callable
    {
        return $this->routes[$this->key($pluginId, strtoupper($method), trim($uri, '/'))] ?? null;
    }

    /**
     * @return list<array{plugin_id: string, method: string, uri: string}>
     */
    public function routesForPlugin(string $pluginId): array
    {
        $routes = [];

        foreach ($this->routes as $key => $handler) {
            unset($handler);

            if (! str_starts_with($key, $pluginId.'|')) {
                continue;
            }

            [, $method, $uri] = explode('|', $key, 3);

            $routes[] = [
                'plugin_id' => $pluginId,
                'method' => $method,
                'uri' => $uri,
            ];
        }

        return $routes;
    }

    public function forgetPlugin(string $pluginId): void
    {
        foreach (array_keys($this->routes) as $key) {
            if (str_starts_with($key, $pluginId.'|')) {
                unset($this->routes[$key]);
            }
        }
    }

    public function clear(): void
    {
        $this->routes = [];
    }

    private function key(string $pluginId, string $method, string $uri): string
    {
        return $pluginId.'|'.$method.'|'.$uri;
    }
}
