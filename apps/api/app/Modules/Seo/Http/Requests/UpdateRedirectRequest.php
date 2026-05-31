<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateRedirectRequest extends FormRequest
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
        $redirect = $this->route('redirect');

        return [
            'from_path' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('redirects', 'from_path')->ignore($redirect?->id),
            ],
            'to_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'to_url' => ['sometimes', 'nullable', 'string', 'max:2048', 'url'],
            'status_code' => ['sometimes', 'integer', Rule::in([301, 302])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $redirect = $this->route('redirect');
            $toPath = $this->input('to_path', $redirect?->to_path);
            $toUrl = $this->input('to_url', $redirect?->to_url);

            if ($this->has('to_path') || $this->has('to_url')) {
                if (! filled($toPath) && ! filled($toUrl)) {
                    $validator->errors()->add('to_path', 'Either to_path or to_url is required.');
                }

                if (filled($toPath) && filled($toUrl)) {
                    $validator->errors()->add('to_path', 'Provide either to_path or to_url, not both.');
                }
            }
        });
    }
}
