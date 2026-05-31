<?php

declare(strict_types=1);

namespace App\Core\Requirements\Contract;

use App\Core\Requirements\RequirementCheck;

interface RequirementEvaluator
{
    public function evaluate(): RequirementCheck;
}
