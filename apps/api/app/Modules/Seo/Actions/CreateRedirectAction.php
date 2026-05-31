<?php

declare(strict_types=1);

namespace App\Modules\Seo\Actions;

use App\Modules\Seo\Models\Redirect;
use App\Modules\Seo\Services\RedirectPathNormalizer;

final class CreateRedirectAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Redirect
    {
        return Redirect::query()->create([
            'from_path' => RedirectPathNormalizer::normalize((string) $data['from_path']),
            'to_path' => isset($data['to_path']) && is_string($data['to_path'])
                ? RedirectPathNormalizer::normalize($data['to_path'])
                : null,
            'to_url' => $data['to_url'] ?? null,
            'status_code' => (int) ($data['status_code'] ?? 301),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }
}
