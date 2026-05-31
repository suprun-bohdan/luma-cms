<?php

declare(strict_types=1);

namespace App\Core\Requirements;

use App\Core\Requirements\Contract\RequirementEvaluator;

final class PhpVersionEvaluator implements RequirementEvaluator
{
    public function __construct(
        private readonly string $currentVersion = PHP_VERSION,
    ) {}

    public function evaluate(): RequirementCheck
    {
        $minimum = (string) config('luma.php.minimum', '8.3.0');
        $recommended = (string) config('luma.php.recommended', '8.4.0');
        /** @var list<string> $supportedBranches */
        $supportedBranches = config('luma.php.supported_branches', ['8.3', '8.4']);

        if (version_compare($this->currentVersion, $minimum, '<')) {
            return new RequirementCheck(
                id: 'php.version',
                label: 'PHP version',
                status: RequirementStatus::Failed,
                message: sprintf(
                    'PHP %s or newer is required (Laravel 13). Current: %s. Select PHP 8.3+ in your hosting panel. PHP 7.x and 8.0–8.2 are not supported.',
                    $minimum,
                    $this->currentVersion,
                ),
            );
        }

        $branch = $this->branch($this->currentVersion);

        if (! in_array($branch, $supportedBranches, true)) {
            return new RequirementCheck(
                id: 'php.version',
                label: 'PHP version',
                status: RequirementStatus::Warning,
                message: sprintf(
                    'PHP %s works but branch %s is not in the tested legacy set (%s). Recommended: %s+.',
                    $this->currentVersion,
                    $branch,
                    implode(', ', $supportedBranches),
                    $recommended,
                ),
            );
        }

        if (version_compare($this->currentVersion, $recommended, '<')) {
            return new RequirementCheck(
                id: 'php.version',
                label: 'PHP version',
                status: RequirementStatus::Warning,
                message: sprintf(
                    'PHP %s is supported. Recommended upgrade to %s+ when your host allows it.',
                    $this->currentVersion,
                    $recommended,
                ),
            );
        }

        return new RequirementCheck(
            id: 'php.version',
            label: 'PHP version',
            status: RequirementStatus::Passed,
            message: sprintf('PHP %s meets requirements.', $this->currentVersion),
        );
    }

    private function branch(string $version): string
    {
        $parts = explode('.', $version);

        return ($parts[0] ?? '0').'.'.($parts[1] ?? '0');
    }
}
