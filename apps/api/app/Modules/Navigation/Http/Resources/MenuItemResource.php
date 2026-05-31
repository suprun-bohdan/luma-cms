<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Modules\Navigation\Models\MenuItem */
final class MenuItemResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'page_slug' => $this->page_slug,
            'url' => $this->url,
            'sort_order' => $this->sort_order,
            'resolved_url' => $this->resolved_url,
        ];
    }
}
