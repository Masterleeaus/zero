<?php

declare(strict_types=1);

namespace App\Models\Equipment;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Premises\Premises;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 8 — Warranty Claim
 *
 * Tracks an individual warranty claim raised against an EquipmentWarranty.
 * A claim may be linked to a ServiceJob that carried out the work, a Customer,
 * and the Premises where the equipment is installed.
 *
 * Status values: draft | submitted | approved | rejected | completed | cancelled
 */
class WarrantyClaim extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'warranty_claims';

    protected $fillable = [
        'company_id',
        'created_by',
        'equipment_warranty_id',
        'service_job_id',
        'customer_id',
        'premises_id',
        'claim_reference',
        'claim_date',
        'provider',
        'status',
        'resolution_notes',
        'resolved_at',
        'approved_flag',
        'rejected_flag',
    ];

    protected $casts = [
        'claim_date'    => 'date',
        'resolved_at'   => 'datetime',
        'approved_flag' => 'boolean',
        'rejected_flag' => 'boolean',
    ];

    protected $attributes = [
        'status'        => 'draft',
        'approved_flag' => false,
        'rejected_flag' => false,
    ];

    // ── Status constants ──────────────────────────────────────────────────────

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(EquipmentWarranty::class, 'equipment_warranty_id');
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REJECTED]);
    }

    public function isApproved(): bool
    {
        return $this->approved_flag || $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->rejected_flag || $this->status === self::STATUS_REJECTED;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED, self::STATUS_REJECTED]);
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForPremises(Builder $query, int $premisesId): Builder
    {
        return $query->where('premises_id', $premisesId);
    }
}
