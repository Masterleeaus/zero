<?php

declare(strict_types=1);

namespace App\Models\FSM;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FsmJobBlocker — a single blocking reason attached to a service job.
 *
 * Multiple blockers can exist for one job.  Resolved blockers are kept for
 * audit purposes.
 */
class FsmJobBlocker extends Model
{
    use BelongsToCompany;

    protected $table = 'fsm_job_blockers';

    protected $fillable = [
        'company_id',
        'service_job_id',
        'blocker_type',
        'blocker_label',
        'details',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    // Known blocker type constants
    public const TYPE_PARTS_MISSING       = 'parts_missing';
    public const TYPE_AGREEMENT_EXPIRED   = 'agreement_expired';
    public const TYPE_EQUIPMENT_FAULT     = 'equipment_fault';
    public const TYPE_CUSTOMER_HOLD       = 'customer_hold';
    public const TYPE_AWAITING_APPROVAL   = 'awaiting_approval';
    public const TYPE_SKILL_MISMATCH      = 'skill_mismatch';
    public const TYPE_CONTRACT_VIOLATION  = 'contract_violation';
    public const TYPE_WARRANTY_EXPIRED    = 'warranty_expired';
    public const TYPE_DEPENDENCY_UNMET    = 'dependency_unmet';
    public const TYPE_TECHNICIAN_UNREADY  = 'technician_unready';

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('is_resolved', true);
    }
}
