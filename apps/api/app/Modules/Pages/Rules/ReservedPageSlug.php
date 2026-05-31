<?php

declare(strict_types=1);

namespace App\Modules\Pages\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ReservedPageSlug implements ValidationRule
{
    private const RESERVED = [
        'admin',
        'api',
        'p',
        'sitemap.xml',
        'robots.txt',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (in_array(strtolower($value), self::RESERVED, true)) {
            $fail('The :attribute is reserved and cannot be used.');
        }
    }
}
