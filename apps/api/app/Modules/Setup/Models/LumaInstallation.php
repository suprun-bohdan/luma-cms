<?php

declare(strict_types=1);

namespace App\Modules\Setup\Models;

use Illuminate\Database\Eloquent\Model;

class LumaInstallation extends Model
{
    protected $table = 'luma_installation';

    protected $fillable = [
        'completed_at',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
