<?php

declare(strict_types=1);

namespace App\Core\Requirements;

use App\Core\Requirements\Contract\RequirementEvaluator;

final class VendorAutoloadEvaluator implements RequirementEvaluator
{
    public function evaluate(): RequirementCheck
    {
        $autoload = base_path('vendor/autoload.php');

        if (! is_file($autoload)) {
            return new RequirementCheck(
                id: 'vendor.autoload',
                label: 'Application vendor bundle',
                status: RequirementStatus::Failed,
                message: 'vendor/autoload.php is missing. Use a release archive with bundled vendor or run composer install locally.',
                messageKey: RequirementCheck::messageKeyFor('vendor.autoload', 'failed'),
            );
        }

        return new RequirementCheck(
            id: 'vendor.autoload',
            label: 'Application vendor bundle',
            status: RequirementStatus::Passed,
            message: 'Vendor autoload is present.',
            messageKey: RequirementCheck::messageKeyFor('vendor.autoload', 'passed'),
        );
    }
}
