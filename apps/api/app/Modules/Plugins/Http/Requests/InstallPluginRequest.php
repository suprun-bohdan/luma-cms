<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InstallPluginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plugin_id' => ['required', 'string', 'max:191'],
        ];
    }
}
