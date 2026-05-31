<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\UpdateSettingsRequest;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Http\JsonResponse;

final class SettingsController extends Controller
{
    public function show(SettingsService $settings): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        return response()->json([
            'data' => [
                'site' => [
                    'title' => $settings->get('site.title', config('app.name')),
                    'tagline' => $settings->get('site.tagline', ''),
                    'locale' => $settings->get('site.locale', 'en'),
                    'timezone' => $settings->get('site.timezone', 'UTC'),
                ],
                'seo' => [
                    'default_title_suffix' => $settings->get('seo.default_title_suffix', ''),
                ],
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request, SettingsService $settings): JsonResponse
    {
        $request->persist($settings);

        return $this->show($settings);
    }
}
