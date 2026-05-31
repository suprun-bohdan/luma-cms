<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Setting;

final class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general'): Setting
    {
        return Setting::query()->updateOrCreate(
            ['key' => $key],
            ['group' => $group, 'value' => $value],
        );
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function allByGroup(string $group = 'general'): array
    {
        return Setting::query()
            ->where('group', $group)
            ->pluck('value', 'key')
            ->all();
    }
}
