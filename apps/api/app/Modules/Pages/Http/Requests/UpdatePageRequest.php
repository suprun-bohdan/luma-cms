<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Requests;

use App\Modules\Pages\Rules\ReservedPageSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePageRequest extends FormRequest
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
        /** @var \App\Modules\Pages\Models\Page|null $page */
        $page = $this->route('page');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($page?->id),
                new ReservedPageSlug(),
            ],
            'template' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'array'],
            'seo' => ['sometimes', 'nullable', 'array'],
            'seo.title' => ['sometimes', 'nullable', 'string', 'max:70'],
            'seo.description' => ['sometimes', 'nullable', 'string', 'max:160'],
            'seo.og_image' => ['sometimes', 'nullable', 'uuid', 'exists:media,uuid'],
        ];
    }
}
