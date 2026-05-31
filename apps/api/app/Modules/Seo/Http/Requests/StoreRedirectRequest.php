<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreRedirectRequest extends FormRequest
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
            'from_path' => ['required', 'string', 'max:255', Rule::unique('redirects', 'from_path')],
            'to_path' => ['nullable', 'string', 'max:255', 'required_without:to_url'],
            'to_url' => ['nullable', 'string', 'max:2048', 'url', 'required_without:to_path'],
            'status_code' => ['sometimes', 'integer', Rule::in([301, 302])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $toPath = $this->input('to_path');
            $toUrl = $this->input('to_url');

            if (filled($toPath) && filled($toUrl)) {
                $validator->errors()->add('to_path', 'Provide either to_path or to_url, not both.');
            }
        });
    }
}
