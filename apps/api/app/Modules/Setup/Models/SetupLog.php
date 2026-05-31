<?php

declare(strict_types=1);

namespace App\Modules\Setup\Models;

use Illuminate\Database\Eloquent\Model;

class SetupLog extends Model
{
    protected $fillable = [
        'step',
        'status',
        'message',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }
}
