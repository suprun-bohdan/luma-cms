<?php

declare(strict_types=1);

namespace Luma\Demo;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        $context->listen('system.booted', static function () use ($context): void {
            $context->log('info', 'Demo plugin received system.booted.');
        });
    }

    public function boot(PluginContext $context): void
    {
        $context->log('info', 'Demo plugin boot complete.');
    }
}
