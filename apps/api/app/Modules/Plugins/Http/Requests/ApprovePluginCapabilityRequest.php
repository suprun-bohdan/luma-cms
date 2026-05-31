<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApprovePluginCapabilityRequest extends FormRequest
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
            'capability' => ['required', 'string', 'max:191'],
        ];
    }
}
