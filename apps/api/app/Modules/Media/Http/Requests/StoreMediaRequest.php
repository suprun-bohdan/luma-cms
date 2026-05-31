<?php

declare(strict_types=1);

namespace App\Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMediaRequest extends FormRequest
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
        $maxKb = (int) config('media.max_upload_kb', 5120);

        return [
            'file' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,gif,pdf', 'max:'.$maxKb],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
