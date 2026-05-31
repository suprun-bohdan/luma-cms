<?php

declare(strict_types=1);

namespace App\Modules\Content\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entry_id',
        'schema_version',
        'data',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'schema_version' => 'integer',
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
