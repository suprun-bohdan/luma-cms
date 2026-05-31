<?php

declare(strict_types=1);

namespace App\Modules\Seo\Actions;

use App\Modules\Seo\Models\Redirect;
use App\Modules\Seo\Services\RedirectPathNormalizer;

final class UpdateRedirectAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Redirect $redirect, array $data): Redirect
    {
        if (array_key_exists('from_path', $data)) {
            $redirect->from_path = RedirectPathNormalizer::normalize((string) $data['from_path']);
        }

        if (array_key_exists('to_path', $data)) {
            $redirect->to_path = is_string($data['to_path']) && $data['to_path'] !== ''
                ? RedirectPathNormalizer::normalize($data['to_path'])
                : null;
        }

        if (array_key_exists('to_url', $data)) {
            $redirect->to_url = is_string($data['to_url']) && $data['to_url'] !== ''
                ? $data['to_url']
                : null;
        }

        if (array_key_exists('status_code', $data)) {
            $redirect->status_code = (int) $data['status_code'];
        }

        if (array_key_exists('is_active', $data)) {
            $redirect->is_active = (bool) $data['is_active'];
        }

        $redirect->save();

        return $redirect->refresh();
    }
}
