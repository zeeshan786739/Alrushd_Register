<?php

namespace App\Services\Platform;

use App\Models\Organization;
use App\Models\PlatformActivityLog;

class PlatformActivityLogger
{
    public static function log(
        string $action,
        string $description,
        ?Organization $organization = null,
        array $metadata = []
    ): void {
        try {
            PlatformActivityLog::create([
                'admin_id' => auth('admin')->id(),
                'organization_id' => $organization?->id,
                'action' => $action,
                'description' => $description,
                'metadata' => $metadata ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
