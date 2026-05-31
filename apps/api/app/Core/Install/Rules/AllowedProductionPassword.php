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

        $allowWeak = request()->boolean('allow_weak_password');

        try {
            app(ProductionPasswordGuard::class)->assertAllowed($value, $allowWeak);
        } catch (\InvalidArgumentException $exception) {
            $message = $exception->getMessage();

            if (str_contains($message, 'at least 12 characters')) {
                $fail('errors.password.minLength');

                return;
            }

            if (str_contains($message, 'not allowed')) {
                $fail('errors.password.blocked');

                return;
            }

            $fail($message);
        }
    }
}
