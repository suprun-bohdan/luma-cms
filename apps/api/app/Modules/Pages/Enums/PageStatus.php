<?php

declare(strict_types=1);

namespace App\Modules\Pages\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
