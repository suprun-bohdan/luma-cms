<?php

declare(strict_types=1);

namespace App\Modules\Content\Services;

use App\Modules\Content\Enums\FieldType;
use App\Modules\Content\Models\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class EntryDataValidator
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function validate(Collection $collection, array $data, bool $enforceRequired = true): array
    {
        $fields = $collection->fields()->orderBy('sort_order')->get();
        $allowedSlugs = $fields->pluck('slug')->all();

        $unknownKeys = array_diff(array_keys($data), $allowedSlugs);
        if ($unknownKeys !== []) {
            throw ValidationException::withMessages([
                'data' => ['Unknown field keys: '.implode(', ', $unknownKeys).'.'],
            ]);
        }

        $rules = [];
        $messages = [];

        foreach ($fields as $field) {
            $key = 'data.'.$field->slug;
            $fieldRules = [$field->required && $enforceRequired ? 'required' : 'nullable'];

            $fieldRules = array_merge($fieldRules, match ($field->type) {
                FieldType::Text, FieldType::Textarea => ['string'],
                FieldType::Number => ['numeric'],
                FieldType::Boolean => ['boolean'],
                FieldType::Datetime => ['date'],
                FieldType::Json => ['array'],
            });

            $rules[$key] = $fieldRules;
            $messages[$key.'.required'] = "The {$field->slug} field is required.";
        }

        Validator::make(['data' => $data], $rules, $messages)->validate();

        return $data;
    }
}
