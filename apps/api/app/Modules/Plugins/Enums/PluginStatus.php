<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Enums;

enum PluginStatus: string
{
    case Installed = 'installed';
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
