<?php

declare(strict_types=1);

namespace App\Modules\Navigation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'label',
        'page_slug',
        'url',
        'sort_order',
    ];

    protected $appends = [
        'resolved_url',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function getResolvedUrlAttribute(): string
    {
        if (is_string($this->page_slug) && $this->page_slug !== '') {
            return '/p/'.$this->page_slug;
        }

        if (is_string($this->url) && $this->url !== '') {
            return $this->url;
        }

        return '#';
    }
}
