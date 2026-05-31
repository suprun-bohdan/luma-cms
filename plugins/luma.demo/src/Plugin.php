<?php

declare(strict_types=1);

namespace Luma\Demo;

use App\Modules\Plugins\Contracts\PluginContext;
use App\Modules\Plugins\Contracts\PluginContract;
use App\Modules\Plugins\Events\ContentLifecycleEvent;

final class Plugin implements PluginContract
{
    public function register(PluginContext $context): void
    {
        $context->listen('system.booted', static function () use ($context): void {
            $context->log('info', 'Demo plugin received system.booted.');
        });

        $context->listen('content.afterPublish', static function (mixed $payload) use ($context): void {
            if (! $payload instanceof ContentLifecycleEvent) {
                return;
            }

            $context->log('info', 'Demo plugin received content.afterPublish.', [
                'entity' => $payload->entity,
                'slug' => $payload->slug,
            ]);
        });

        $context->registerAdminNavigation('Demo insights', '/plugins', 90);

        $context->registerBlockType(
            'quote',
            'Quote',
            'Pull quote with optional author attribution',
            'content',
            [
                'quote' => 'Great products are built by teams who care about structured content.',
                'author' => 'Luma CMS',
            ],
            [
                ['name' => 'quote', 'label' => 'Quote', 'type' => 'textarea'],
                ['name' => 'author', 'label' => 'Author', 'type' => 'text'],
            ],
            static function (array $props): string {
                $quote = e(is_string($props['quote'] ?? null) ? $props['quote'] : '');
                $author = e(is_string($props['author'] ?? null) ? $props['author'] : '');

                $footer = $author !== '' ? '<footer>&mdash; '.$author.'</footer>' : '';

                return '<blockquote class="luma-block-quote"><p>'.$quote.'</p>'.$footer.'</blockquote>';
            },
        );
    }

    public function boot(PluginContext $context): void
    {
        $context->log('info', 'Demo plugin boot complete.');
    }
}
