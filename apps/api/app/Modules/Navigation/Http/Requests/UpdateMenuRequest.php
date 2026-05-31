<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateMenuRequest extends FormRequest
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
        /** @var \App\Modules\Navigation\Models\Menu|null $menu */
        $menu = $this->route('menu');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('menus', 'slug')->ignore($menu?->id),
            ],
            'items' => ['sometimes', 'array'],
            'items.*.label' => ['required_with:items', 'string', 'max:255'],
            'items.*.page_slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'items.*.url' => ['nullable', 'string', 'max:2048', 'url'],
            'items.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
