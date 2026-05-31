<?php

declare(strict_types=1);

namespace Luma\TestRoutes;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;
use Illuminate\Http\Request;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        $context->registerRoute('GET', 'ping', static function (Request $request): array {
            return [
                'ok' => true,
                'plugin_id' => 'luma.test-routes',
            ];
        });
    }

    public function boot(PluginContext $context): void
    {
        //
    }
}
