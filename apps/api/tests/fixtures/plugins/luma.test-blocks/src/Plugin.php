<?php

declare(strict_types=1);

namespace Luma\TestBlocks;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        $context->registerBlockType(
            'banner',
            'Test banner',
            'Simple banner block for tests',
            'content',
            ['text' => 'Plugin banner text'],
            [
                ['name' => 'text', 'label' => 'Text', 'type' => 'text'],
            ],
            static function (array $props): string {
                $text = e(is_string($props['text'] ?? null) ? $props['text'] : '');

                return '<div class="luma-test-banner">'.$text.'</div>';
            },
        );
    }

    public function boot(PluginContext $context): void
    {
        //
    }
}
