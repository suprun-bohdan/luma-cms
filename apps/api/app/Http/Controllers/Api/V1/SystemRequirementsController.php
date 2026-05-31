<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Core\Requirements\EnvironmentRequirementChecker;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class SystemRequirementsController extends Controller
{
    public function __invoke(EnvironmentRequirementChecker $requirements): JsonResponse
    {
        $report = $requirements->check();

        return response()->json([
            'passed' => $report->passed(),
            'checks' => $report->toArray(),
        ]);
    }
}
