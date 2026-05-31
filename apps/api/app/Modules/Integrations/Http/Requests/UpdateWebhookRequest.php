<?php

declare(strict_types=1);

namespace App\Modules\Integrations\Http\Requests;

use App\Modules\Integrations\Services\IntegrationEventCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'string', 'url', 'max:2048', 'regex:/^https:\/\//i'],
            'events' => ['sometimes', 'array', 'min:1'],
            'events.*' => ['required', 'string', Rule::in(app(IntegrationEventCatalog::class)->all())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
