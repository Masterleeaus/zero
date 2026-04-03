<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FSM Module 9 — Repair Resolution
 *
 * Documents the final outcome of a RepairOrder, including root cause,
 * preventive actions, verification, and any required follow-up.
 */
class RepairResolution extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'repair_resolutions';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_order_id',
        'resolution_type',
        'resolution_notes',
        'root_cause',
        'preventive_action',
        'follow_up_required',
        'follow_up_notes',
        'resolved_by',
        'resolved_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'follow_up_required' => 'boolean',
        'resolved_at'        => 'datetime',
        'verified_at'        => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
