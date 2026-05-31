<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCollectionRequest extends FormRequest
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
        $collectionId = $this->route('collection')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('collections', 'slug')->ignore($collectionId),
            ],
            'description' => ['nullable', 'string'],
            'config' => ['nullable', 'array'],
            'schema_version' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
