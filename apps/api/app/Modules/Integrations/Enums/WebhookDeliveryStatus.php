<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Enums;

enum WebhookDeliveryStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
}
