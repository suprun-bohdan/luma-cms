<?php

declare(strict_types=1);

namespace App\Modules\Setup\Http\Requests;

use App\Core\Install\Rules\AllowedProductionPassword;
use Illuminate\Foundation\Http\FormRequest;

final class SetupFinishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'site_title' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'max:255', new AllowedProductionPassword()],
            'with_starter_site' => ['sometimes', 'boolean'],
        ];
    }
}
