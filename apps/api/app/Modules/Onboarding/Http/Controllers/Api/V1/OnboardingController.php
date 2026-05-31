<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Settings\Models\Setting;
use App\Modules\Setup\Models\SetupLog;
use App\Modules\Setup\Services\SetupLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final class OnboardingController extends Controller
{
    public function progress(OnboardingService $onboarding): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        return response()->json([
            'data' => $onboarding->progress(),
        ]);
    }

    public function journal(SetupLogService $setupLog): JsonResponse
    {
        $this->authorize('viewAny', SetupLog::class);

        $logs = array_values(array_filter(
            $setupLog->recent(50),
            static fn (array $entry): bool => str_starts_with($entry['step'], 'onboarding.'),
        ));

        return response()->json([
            'data' => array_slice($logs, -10),
        ]);
    }

    public function welcome(Request $request, OnboardingService $onboarding): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $validated = $request->validate([
            'site_title' => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:64'],
        ]);

        $onboarding->completeWelcome($validated['site_title'], $validated['industry']);

        return $this->progress($onboarding);
    }

    public function siteType(Request $request, OnboardingService $onboarding): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $validated = $request->validate([
            'preset' => ['required', 'string', 'in:business,blog,portfolio'],
        ]);

        try {
            $onboarding->completeSiteType($validated['preset']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return $this->progress($onboarding);
    }

    public function starter(Request $request, OnboardingService $onboarding): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        try {
            $onboarding->installStarterContent($request->user());
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return $this->progress($onboarding);
    }

    public function integrations(OnboardingService $onboarding): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $onboarding->skipIntegrations();

        return $this->progress($onboarding);
    }

    public function finish(OnboardingService $onboarding): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $onboarding->finish();

        return $this->progress($onboarding);
    }
}
