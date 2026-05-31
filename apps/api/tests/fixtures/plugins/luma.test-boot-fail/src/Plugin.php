<?php

declare(strict_types=1);

namespace Luma\TestBootFail;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;
use RuntimeException;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        //
    }

    public function boot(PluginContext $context): void
    {
        throw new RuntimeException('Intentional boot failure for tests.');
    }
}
