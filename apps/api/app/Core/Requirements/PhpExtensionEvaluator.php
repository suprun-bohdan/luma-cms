<?php

declare(strict_types=1);

namespace App\Core\Requirements;

use App\Core\Requirements\Contract\RequirementEvaluator;

final class PhpExtensionEvaluator implements RequirementEvaluator
{
    public function evaluate(): RequirementCheck
    {
        /** @var list<string> $required */
        $required = config('luma.extensions.required', []);

        $missing = array_values(array_filter(
            $required,
            static fn (string $extension): bool => ! extension_loaded($extension),
        ));

        if ($missing !== []) {
            return new RequirementCheck(
                id: 'php.extensions',
                label: 'PHP extensions',
                status: RequirementStatus::Failed,
                message: 'Missing required extensions: '.implode(', ', $missing),
            );
        }

        return new RequirementCheck(
            id: 'php.extensions',
            label: 'PHP extensions',
            status: RequirementStatus::Passed,
            message: 'All required PHP extensions are loaded.',
        );
    }
}
