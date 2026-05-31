<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Http\Resources;

use App\Modules\Plugins\Models\PluginCapability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PluginCapability */
final class PluginCapabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'capability' => $this->capability,
            'granted' => $this->granted,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
