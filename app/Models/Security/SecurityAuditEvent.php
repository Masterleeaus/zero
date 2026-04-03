<?php

declare(strict_types=1);

namespace App\Models\Security;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * Tenant-aware security audit event log.
 *
 * Every security-relevant action (lockout, IP block, session revoke, policy
 * gate denial, token lifecycle) is persisted here, scoped to company_id so
 * each tenant sees only its own audit trail.
 *
 * @property int         $id
 * @property int|null    $company_id
 * @property int|null    $user_id
 * @property string      $event_type
 * @property string|null $ip_address
 * @property string|null $email
 * @property array|null  $context
 * @property \Illuminate\Support\Carbon $created_at
 */
class SecurityAuditEvent extends Model
{
    use BelongsToCompany;

    protected $table = 'security_audit_events';

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = [
        'context' => 'array',
    ];

    // ── event_type constants ───────────────────────────────────────────────

    public const TYPE_LOGIN_LOCKOUT      = 'login_lockout';
    public const TYPE_IP_BLOCKED         = 'ip_blocked';
    public const TYPE_EMAIL_BLOCKED      = 'email_blocked';
    public const TYPE_SESSION_REVOKED    = 'session_revoked';
    public const TYPE_LOGIN_EXPIRY       = 'login_expiry';
    public const TYPE_DIFFERENT_IP       = 'different_ip';
    public const TYPE_RATE_LIMIT         = 'rate_limit';
    public const TYPE_POLICY_DENIED      = 'policy_denied';
    public const TYPE_TOKEN_REVOKED      = 'token_revoked';
    public const TYPE_DEVICE_UNTRUSTED   = 'device_untrusted';
    public const TYPE_SIGNAL_REJECTED    = 'signal_rejected';
}
