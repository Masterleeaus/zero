<?php

declare(strict_types=1);

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Titan Admin Audit Log
 *
 * Reads from the tz_audit_log table created by the TitanSignals migration.
 * Extended with company_id (via migration 800100) for tenant-scoped queries.
 */
class AdminAuditLog extends Model
{
    protected $table = 'tz_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'process_id',
        'signal_id',
        'action',
        'performed_by',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }
}
