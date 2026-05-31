<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Contracts;

interface PluginContract
{
    public function register(PluginContext $context): void;

    public function boot(PluginContext $context): void;
}
