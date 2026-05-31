<?php

declare(strict_types=1);

namespace App\Core\Install\Rules;

use App\Core\Install\ProductionPasswordGuard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class AllowedProductionPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        try {
            app(ProductionPasswordGuard::class)->assertAllowed($value);
        } catch (\InvalidArgumentException $exception) {
            $fail($exception->getMessage());
        }
    }
}
