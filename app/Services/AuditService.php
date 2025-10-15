<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Log an action to the audit trail
     *
     * @param string $action The action being performed (e.g., 'wager.created')
     * @param Model|null $auditable The entity being acted upon
     * @param array $metadata Additional context data
     * @param User|null $actor The user performing the action (null = system)
     * @param string|null $ipAddress IP address of the actor
     * @param string|null $userAgent User agent string
     * @return AuditLog
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        array $metadata = [],
        ?User $actor = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? 'User' : 'System',
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log from a request context (auto-captures IP and user agent)
     */
    public static function logFromRequest(
        string $action,
        ?Model $auditable = null,
        array $metadata = [],
        ?User $actor = null
    ): AuditLog {
        return self::log(
            action: $action,
            auditable: $auditable,
            metadata: $metadata,
            actor: $actor,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        );
    }

    /**
     * Log a system action (no actor)
     */
    public static function logSystem(
        string $action,
        ?Model $auditable = null,
        array $metadata = []
    ): AuditLog {
        return self::log(
            action: $action,
            auditable: $auditable,
            metadata: $metadata,
            actor: null
        );
    }
}
