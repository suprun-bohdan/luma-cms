<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePageRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('pages', 'slug')],
            'template' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'array'],
            'seo' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
