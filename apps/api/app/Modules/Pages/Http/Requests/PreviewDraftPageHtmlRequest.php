<?php

declare(strict_types=1);

namespace App\Modules\Pages\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PreviewDraftPageHtmlRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['sometimes', 'array'],
            'seo' => ['sometimes', 'nullable', 'array'],
            'seo.title' => ['sometimes', 'string', 'max:255'],
            'seo.description' => ['sometimes', 'string', 'max:500'],
            'seo.og_image' => ['nullable', 'string', 'uuid'],
        ];
    }
}
