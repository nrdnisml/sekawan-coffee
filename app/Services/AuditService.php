<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Log system activity.
     */
    public function log(?int $userId, string $action, string $entity, ?int $entityId, ?string $description = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            // Fail-safe: log to Laravel logs if database logging fails
            Log::error("Failed to create activity log: " . $e->getMessage());
        }
    }
}
