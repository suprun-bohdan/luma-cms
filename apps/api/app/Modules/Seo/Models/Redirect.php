<?php

declare(strict_types=1);

namespace App\Modules\Seo\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected $fillable = [
        'from_path',
        'to_path',
        'to_url',
        'status_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function resolveTargetUrl(): string
    {
        if (is_string($this->to_url) && $this->to_url !== '') {
            return $this->to_url;
        }

        if (is_string($this->to_path) && $this->to_path !== '') {
            return url($this->to_path);
        }

        return url('/');
    }

    protected static function newFactory(): RedirectFactory
    {
        return RedirectFactory::new();
    }
}
