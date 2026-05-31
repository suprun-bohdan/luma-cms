<?php

declare(strict_types=1);

namespace App\Modules\Forms\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case Email = 'email';
    case Textarea = 'textarea';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
