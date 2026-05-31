<?php

declare(strict_types=1);

namespace App\Modules\Forms\Actions;

use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Models\FormSubmission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class SubmitFormAction
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(Form $form, array $input, ?string $ip, ?string $userAgent): ?FormSubmission
    {
        $honeypot = $input['_hp'] ?? null;
        unset($input['_hp']);

        if (is_string($honeypot) && trim($honeypot) !== '') {
            return null;
        }

        if (! $form->is_active) {
            abort(404);
        }

        $form->load('fields');

        $rules = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            $fieldRules = [];

            if ($field->required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $fieldRules[] = 'string';
            $fieldRules[] = 'max:10000';

            if ($field->type->value === 'email') {
                $fieldRules[] = 'email';
            }

            $rules[$field->name] = $fieldRules;
            $attributes[$field->name] = $field->label;
        }

        $validator = Validator::make($input, $rules, [], $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return FormSubmission::query()->create([
            'form_id' => $form->id,
            'data' => $validated,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
