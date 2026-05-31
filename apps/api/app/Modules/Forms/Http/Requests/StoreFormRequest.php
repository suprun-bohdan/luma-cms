<?php

declare(strict_types=1);

namespace App\Modules\Forms\Http\Requests;

use App\Modules\Forms\Enums\FormFieldType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreFormRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('forms', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
            'fields' => ['sometimes', 'array'],
            'fields.*.name' => ['required_with:fields', 'string', 'max:255', 'alpha_dash'],
            'fields.*.label' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.type' => ['required_with:fields', Rule::in(FormFieldType::values())],
            'fields.*.required' => ['sometimes', 'boolean'],
            'fields.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
