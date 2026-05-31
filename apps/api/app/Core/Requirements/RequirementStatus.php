<?php

declare(strict_types=1);

namespace App\Core\Requirements;

enum RequirementStatus: string
{
    case Passed = 'passed';
    case Warning = 'warning';
    case Failed = 'failed';
}
