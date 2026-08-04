<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Models\SaaS\AuditLog;
use Illuminate\Support\Facades\DB;

final class AuditLogService
{
    public function record(string $event, string $severity = 'info', array $context = [], ?int $userId = null, ?int $organizationId = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        if ($userId !== null && ! DB::table('users')->where('id', $userId)->exists()) {
            $userId = null;
        }

        AuditLog::query()->create([
            'event' => $event,
            'severity' => $severity,
            'context' => $context,
            'user_id' => $userId,
            'organization_id' => $organizationId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'occurred_at' => now(),
        ]);
    }
}
