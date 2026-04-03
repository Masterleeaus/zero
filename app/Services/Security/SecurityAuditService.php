<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\Security\SecurityAuditEvent;
use Illuminate\Support\Facades\Auth;

/**
 * SecurityAuditService
 *
 * Centralised writer for the tenant-aware security_audit_events table.
 * All security-relevant actions (lockouts, IP blocks, session revocations,
 * policy denials, token lifecycle events, device-trust failures, signal
 * rejections) must be recorded through this service.
 *
 * Company-scoping is applied automatically from the authenticated user when
 * a company_id is not supplied explicitly.
 */
class SecurityAuditService
{
    /**
     * Record a security audit event.
     *
     * @param  string               $eventType  One of SecurityAuditEvent::TYPE_* constants
     * @param  array<string, mixed> $context    Arbitrary metadata (merged into context JSON)
     * @param  string|null          $ipAddress  Remote IP — defaults to request()->ip() if available
     * @param  string|null          $email      Email address involved in the event
     * @param  int|null             $userId     Performing / target user ID
     * @param  int|null             $companyId  Tenant ID — resolved from auth user when null
     */
    public function record(
        string $eventType,
        array $context = [],
        ?string $ipAddress = null,
        ?string $email = null,
        ?int $userId = null,
        ?int $companyId = null,
    ): SecurityAuditEvent {
        $user      = Auth::user();
        $companyId = $companyId ?? ($user ? data_get($user, 'company_id') : null);
        $userId    = $userId    ?? $user?->getAuthIdentifier();
        $ipAddress = $ipAddress ?? (app()->bound('request') ? request()->ip() : null);

        return SecurityAuditEvent::withoutGlobalScopes()->create([
            'company_id'  => $companyId,
            'user_id'     => $userId,
            'event_type'  => $eventType,
            'ip_address'  => $ipAddress,
            'email'       => $email,
            'context'     => $context ?: null,
            'created_at'  => now(),
        ]);
    }

    /**
     * Retrieve the recent audit trail for a given tenant.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SecurityAuditEvent>
     */
    public function recentForCompany(int $companyId, int $limit = 100)
    {
        return SecurityAuditEvent::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieve security events of a specific type for a tenant.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SecurityAuditEvent>
     */
    public function eventsOfType(int $companyId, string $eventType, int $limit = 50)
    {
        return SecurityAuditEvent::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('event_type', $eventType)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
