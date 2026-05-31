<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginCapability extends Model
{
    protected $fillable = [
        'plugin_id',
        'capability',
        'granted',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
