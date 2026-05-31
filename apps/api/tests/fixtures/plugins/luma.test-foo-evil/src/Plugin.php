<?php

declare(strict_types=1);

namespace Luma\TestFooEvil;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        //
    }

    public function boot(PluginContext $context): void
    {
        //
    }
}
