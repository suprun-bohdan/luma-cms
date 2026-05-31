<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function update(Request $request, SettingsService $settings): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $request->validate([
            'site.title' => ['sometimes', 'string', 'max:255'],
            'site.tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'site.locale' => ['sometimes', 'string', 'max:16'],
            'site.timezone' => ['sometimes', 'string', 'max:64'],
            'seo.default_title_suffix' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $writable = [
            'site.title',
            'site.tagline',
            'site.locale',
            'site.timezone',
            'seo.default_title_suffix',
        ];

        foreach ($writable as $key) {
            $value = $this->settingInput($request, $key);

            if ($value === null && ! $this->hasSettingInput($request, $key)) {
                continue;
            }

            $settings->set(
                $key,
                $value,
                str_starts_with($key, 'seo.') ? 'seo' : 'general',
            );
        }

        return $this->show($settings);
    }

    private function hasSettingInput(Request $request, string $key): bool
    {
        if (array_key_exists($key, $request->all())) {
            return true;
        }

        return $request->has($key);
    }

    private function settingInput(Request $request, string $key): mixed
    {
        if (array_key_exists($key, $request->all())) {
            return $request->all()[$key];
        }

        return $request->input($key);
    }
}
