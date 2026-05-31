<?php

declare(strict_types=1);

namespace App\Modules\Content\Models;

use App\Modules\Content\Enums\FieldType;
use Database\Factories\FieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Field extends Model
{
    /** @use HasFactory<FieldFactory> */
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'name',
        'slug',
        'type',
        'config',
        'sort_order',
        'required',
    ];

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'config' => 'array',
            'sort_order' => 'integer',
            'required' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    protected static function newFactory(): FieldFactory
    {
        return FieldFactory::new();
    }
}
