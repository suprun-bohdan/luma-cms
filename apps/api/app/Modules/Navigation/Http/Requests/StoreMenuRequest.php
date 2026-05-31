<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMenuRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('menus', 'slug')],
            'items' => ['sometimes', 'array'],
            'items.*.label' => ['required_with:items', 'string', 'max:255'],
            'items.*.page_slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'items.*.url' => ['nullable', 'string', 'max:2048', 'url'],
            'items.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
