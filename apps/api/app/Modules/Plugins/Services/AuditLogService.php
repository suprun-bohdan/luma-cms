<?php

declare(strict_types=1);

namespace App\Modules\Plugins\Services;

use App\Models\User;
use App\Modules\Plugins\Models\AuditLog;

final class AuditLogService
{
    public function record(
        string $action,
        string $subjectType,
        string $subjectId,
        ?User $actor = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata === [] ? null : $metadata,
            'created_at' => now(),
        ]);
    }
}
