<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Resources;

use App\Modules\Plugins\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Plugin */
final class PluginResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'plugin_id' => $this->plugin_id,
            'name' => $this->name,
            'version' => $this->version,
            'status' => $this->status->value,
            'path' => $this->path,
            'last_error' => $this->last_error,
            'installed_at' => $this->installed_at?->toIso8601String(),
            'enabled_at' => $this->enabled_at?->toIso8601String(),
            'capabilities' => PluginCapabilityResource::collection($this->whenLoaded('capabilities')),
        ];
    }
}
