<?php

declare(strict_types=1);

namespace Luma\TestHooks;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;
use App\Modules\Plugins\Events\ContentLifecycleEvent;
use Illuminate\Support\Facades\Cache;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        $context->listen('content.afterPublish', static function (mixed $payload) use ($context): void {
            if (! $payload instanceof ContentLifecycleEvent) {
                return;
            }

            Cache::put('luma.test-hooks.last_publish', [
                'entity' => $payload->entity,
                'slug' => $payload->slug,
                'plugin_id' => $context->pluginId(),
            ]);
        });
    }

    public function boot(PluginContext $context): void
    {
        //
    }
}
