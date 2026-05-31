<?php

declare(strict_types=1);

namespace App\Modules\Setup\Services;

use App\Modules\Setup\Models\SetupLog;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SetupLogService
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function write(string $step, string $status, string $message, ?array $context = null): SetupLog
    {
        try {
            if (! Schema::hasTable('setup_logs')) {
                return new SetupLog([
                    'step' => $step,
                    'status' => $status,
                    'message' => $message,
                    'context' => $context,
                ]);
            }

            return SetupLog::query()->create([
                'step' => $step,
                'status' => $status,
                'message' => $message,
                'context' => $context,
            ]);
        } catch (Throwable) {
            return new SetupLog([
                'step' => $step,
                'status' => $status,
                'message' => $message,
                'context' => $context,
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recent(int $limit = 50): array
    {
        try {
            if (! Schema::hasTable('setup_logs')) {
                return [];
            }

            return SetupLog::query()
                ->latest('id')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values()
                ->map(static fn (SetupLog $log): array => [
                    'id' => $log->id,
                    'step' => $log->step,
                    'status' => $log->status,
                    'message' => $log->message,
                    'context' => $log->context,
                    'created_at' => $log->created_at?->toIso8601String(),
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }
}
