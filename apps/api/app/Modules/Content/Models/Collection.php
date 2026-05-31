<?php

declare(strict_types=1);

namespace App\Modules\Content\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'config',
        'schema_version',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'schema_version' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    protected static function newFactory(): CollectionFactory
    {
        return CollectionFactory::new();
    }
}
