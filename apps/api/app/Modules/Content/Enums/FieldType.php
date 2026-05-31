<?php

declare(strict_types=1);

namespace App\Modules\Content\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Boolean = 'boolean';
    case Datetime = 'datetime';
    case Json = 'json';
}
