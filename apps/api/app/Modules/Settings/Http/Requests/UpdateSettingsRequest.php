<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateSettingsRequest extends FormRequest
{
    /** @var list<string> */
    private const WRITABLE_KEYS = [
        'site.title',
        'site.tagline',
        'site.locale',
        'site.timezone',
        'seo.default_title_suffix',
    ];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Setting::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'site.title' => ['sometimes', 'string', 'max:255'],
            'site.tagline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'site.locale' => ['sometimes', 'string', 'max:16'],
            'site.timezone' => ['sometimes', 'string', 'max:64'],
            'seo.default_title_suffix' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function persist(SettingsService $settings): void
    {
        foreach (self::WRITABLE_KEYS as $key) {
            $value = $this->settingInput($key);

            if ($value === null && ! $this->hasSettingInput($key)) {
                continue;
            }

            $settings->set(
                $key,
                $value,
                str_starts_with($key, 'seo.') ? 'seo' : 'general',
            );
        }
    }

    private function hasSettingInput(string $key): bool
    {
        if (array_key_exists($key, $this->all())) {
            return true;
        }

        return $this->has($key);
    }

    private function settingInput(string $key): mixed
    {
        if (array_key_exists($key, $this->all())) {
            return $this->all()[$key];
        }

        return $this->input($key);
    }
}
