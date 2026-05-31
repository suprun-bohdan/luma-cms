<?php

declare(strict_types=1);

namespace App\Modules\Seo\Http\Resources;

use App\Modules\Seo\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Redirect */
final class RedirectResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_path' => $this->from_path,
            'to_path' => $this->to_path,
            'to_url' => $this->to_url,
            'status_code' => $this->status_code,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
