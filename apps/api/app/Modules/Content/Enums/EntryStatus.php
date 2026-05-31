<?php

declare(strict_types=1);

namespace App\Modules\Content\Enums;

enum EntryStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
