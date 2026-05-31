<?php

declare(strict_types=1);

namespace App\Modules\Content\Http\Requests;

use App\Modules\Content\Enums\FieldType;
use App\Modules\Content\Models\Collection;
use App\Modules\Content\Models\Field;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFieldRequest extends FormRequest
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
        /** @var Collection $collection */
        $collection = $this->route('collection');

        /** @var Field $field */
        $field = $this->route('field');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('fields', 'slug')
                    ->where('collection_id', $collection->id)
                    ->ignore($field->id),
            ],
            'type' => ['sometimes', Rule::enum(FieldType::class)],
            'config' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'required' => ['sometimes', 'boolean'],
        ];
    }
}
