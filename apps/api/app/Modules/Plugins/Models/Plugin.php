<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Models;

use App\Modules\Plugins\Enums\PluginStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plugin extends Model
{
    protected $fillable = [
        'plugin_id',
        'name',
        'version',
        'status',
        'manifest',
        'path',
        'installed_at',
        'enabled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PluginStatus::class,
            'manifest' => 'array',
            'installed_at' => 'datetime',
            'enabled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'plugin_id';
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(PluginCapability::class);
    }

    public function isEnabled(): bool
    {
        return $this->status === PluginStatus::Enabled;
    }
}
