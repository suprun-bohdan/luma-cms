<?php

declare(strict_types=1);

namespace App\Core\Requirements;

use App\Core\Requirements\Contract\RequirementEvaluator;

final class EnvironmentRequirementChecker
{
    /**
     * @param  list<RequirementEvaluator>  $evaluators
     */
    public function __construct(
        private readonly array $evaluators,
    ) {}

    public static function default(): self
    {
        return new self([
            new PhpVersionEvaluator(),
            new PhpExtensionEvaluator(),
            new WritablePathEvaluator(),
            new VendorAutoloadEvaluator(),
        ]);
    }

    public function check(): RequirementReport
    {
        $checks = array_map(
            static fn (RequirementEvaluator $evaluator): RequirementCheck => $evaluator->evaluate(),
            $this->evaluators,
        );

        return new RequirementReport($checks);
    }
}
