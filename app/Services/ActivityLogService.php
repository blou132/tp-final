<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    public function log(
        string $action,
        string $entityType,
        int|string $entityId,
        ?int $actorId,
        array $metadata = []
    ): void {
        if (! extension_loaded('mongodb')) {
            return;
        }

        if (! config('database.connections.mongodb.database')) {
            return;
        }

        try {
            ActivityLog::query()->create([
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => (string) $entityId,
                'actor_id' => $actorId,
                'metadata' => $metadata,
            ]);
        } catch (Throwable $exception) {
            Log::warning('MongoDB activity log unavailable', [
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
