<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Contracts\SchedulableEntity;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Equipment\Equipment;
use App\Models\Equipment\InstalledEquipment;
use App\Models\Equipment\WarrantyClaim;
use App\Models\Facility\SiteAsset;
use App\Models\Premises\Premises;
use App\Models\Team\Team;
use App\Models\User;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Services\Repair\RepairTemplateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 9 — Repair Order
 *
 * The central aggregate for a repair lifecycle. Tracks diagnosis, tasks,
 * parts usage, checklists, and resolution. Integrates with equipment,
 * warranty claims, service jobs, and the scheduling surface.
 */
class RepairOrder extends Model implements SchedulableEntity
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'repair_orders';

    // ── Status constants ──────────────────────────────────────────────────────

    public const STATUS_DRAFT             = 'draft';
    public const STATUS_DIAGNOSED         = 'diagnosed';
    public const STATUS_AWAITING_PARTS    = 'awaiting_parts';
    public const STATUS_SCHEDULED         = 'scheduled';
    public const STATUS_IN_PROGRESS       = 'in_progress';
    public const STATUS_PAUSED            = 'paused';
    public const STATUS_AWAITING_APPROVAL = 'awaiting_approval';
    public const STATUS_COMPLETED         = 'completed';
    public const STATUS_VERIFIED          = 'verified';
    public const STATUS_CLOSED            = 'closed';
    public const STATUS_CANCELLED         = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_DIAGNOSED,
        self::STATUS_AWAITING_PARTS,
        self::STATUS_SCHEDULED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PAUSED,
        self::STATUS_AWAITING_APPROVAL,
        self::STATUS_COMPLETED,
        self::STATUS_VERIFIED,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
    ];

    // ── Repair type constants ─────────────────────────────────────────────────

    public const TYPE_BREAKDOWN  = 'breakdown';
    public const TYPE_PREVENTIVE = 'preventive';
    public const TYPE_WARRANTY   = 'warranty';
    public const TYPE_CORRECTIVE = 'corrective';
    public const TYPE_EMERGENCY  = 'emergency';

    // ── Priority constants ────────────────────────────────────────────────────

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_number',
        'equipment_id',
        'installed_equipment_id',
        'site_asset_id',
        'premises_id',
        'service_job_id',
        'warranty_claim_id',
        'agreement_id',
        'customer_id',
        'assigned_team_id',
        'assigned_user_id',
        'repair_template_id',
        'priority',
        'severity',
        'fault_category',
        'repair_type',
        'repair_status',
        'requires_parts',
        'requires_followup',
        'requires_quote',
        'requires_return_visit',
        'diagnosis_summary',
        'resolution_summary',
        'scheduled_at',
        'started_at',
        'completed_at',
        'verified_at',
    ];

    protected $casts = [
        'requires_parts'         => 'boolean',
        'requires_followup'      => 'boolean',
        'requires_quote'         => 'boolean',
        'requires_return_visit'  => 'boolean',
        'scheduled_at'           => 'datetime',
        'started_at'             => 'datetime',
        'completed_at'           => 'datetime',
        'verified_at'            => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function installedEquipment(): BelongsTo
    {
        return $this->belongsTo(InstalledEquipment::class, 'installed_equipment_id');
    }

    public function siteAsset(): BelongsTo
    {
        return $this->belongsTo(SiteAsset::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class);
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class);
    }

    /**
     * The service job that triggered this repair (alias for semantic clarity).
     */
    public function originatingServiceJob(): BelongsTo
    {
        return $this->serviceJob();
    }

    /**
     * A follow-up service job scheduled as a result of this repair.
     *
     * NOTE: This is a best-effort lookup via shared warranty_claim_id.
     * A dedicated `repair_order_id` FK on service_jobs would provide a
     * more robust link — defer to a future service_jobs schema extension pass.
     */
    public function followupServiceJob(): HasOne
    {
        return $this->hasOne(ServiceJob::class, 'warranty_claim_id', 'warranty_claim_id')
            ->whereNotNull('warranty_claim_id')
            ->where('is_warranty_job', true);
    }

    public function warrantyClaim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RepairTemplate::class, 'repair_template_id');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(RepairDiagnosis::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(RepairTask::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(RepairAction::class);
    }

    public function partUsages(): HasMany
    {
        return $this->hasMany(RepairPartUsage::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(RepairChecklist::class);
    }

    public function resolution(): HasOne
    {
        return $this->hasOne(RepairResolution::class);
    }

    // ── Business methods ──────────────────────────────────────────────────────

    public function isWarrantyCovered(): bool
    {
        return $this->repair_type === self::TYPE_WARRANTY && $this->warranty_claim_id !== null;
    }

    public function requiresClaim(): bool
    {
        return $this->repair_type === self::TYPE_WARRANTY
            && $this->equipment !== null
            && $this->equipment->hasWarranty();
    }

    public function claimEligible(): bool
    {
        if (! $this->warrantyClaim) {
            return false;
        }

        return ! in_array($this->warrantyClaim->status ?? '', ['completed', 'cancelled'], true);
    }

    public function coverageSummary(): array
    {
        return [
            'is_warranty' => $this->isWarrantyCovered(),
            'claim_id'    => $this->warranty_claim_id,
            'type'        => $this->repair_type,
            'status'      => $this->repair_status,
        ];
    }

    public function invoiceImpact(): array
    {
        return [
            'requires_invoice' => ! $this->isWarrantyCovered(),
            'covered_amount'   => 0,
            'customer_payable' => true,
        ];
    }

    public function claimRecoveryAmount(): float
    {
        return 0.0;
    }

    public function customerPayableAmount(): float
    {
        return 0.0;
    }

    public function toScheduleEvent(): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->getSchedulableTitle(),
            'start'         => $this->scheduled_at?->toIso8601String(),
            'end'           => $this->completed_at?->toIso8601String(),
            'color'         => '#dc2626',
            'extendedProps' => [
                'type'     => $this->getSchedulableType(),
                'status'   => $this->repair_status,
                'priority' => $this->priority,
            ],
        ];
    }

    public function schedulePriorityScore(): int
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 4,
            self::PRIORITY_HIGH   => 3,
            self::PRIORITY_NORMAL => 2,
            self::PRIORITY_LOW    => 1,
            default               => 2,
        };
    }

    public static function fromTemplate(RepairTemplate $template): static
    {
        return app(RepairTemplateService::class)->createRepairOrder($template);
    }

    // ── SchedulableEntity ─────────────────────────────────────────────────────

    public function getScheduledStart(): ?string
    {
        return $this->scheduled_at?->toIso8601String();
    }

    public function getScheduledEnd(): ?string
    {
        return $this->completed_at?->toIso8601String();
    }

    public function getAssignedUserId(): ?int
    {
        return $this->assigned_user_id;
    }

    public function getSchedulableStatus(): string
    {
        return $this->repair_status ?? self::STATUS_DRAFT;
    }

    public function getSchedulablePriority(): string|int|null
    {
        return $this->priority;
    }

    public function getSchedulableTitle(): string
    {
        $suffix = $this->equipment ? ' - ' . $this->equipment->name : '';

        return 'Repair #' . $this->repair_number . $suffix;
    }

    public function getSchedulableType(): string
    {
        return static::class;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('repair_status', [
            self::STATUS_COMPLETED,
            self::STATUS_VERIFIED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeForCustomer(Builder $query, int $id): Builder
    {
        return $query->where('customer_id', $id);
    }

    public function scopeForPremises(Builder $query, int $id): Builder
    {
        return $query->where('premises_id', $id);
    }

    public function scopeWarranty(Builder $query): Builder
    {
        return $query->where('repair_type', self::TYPE_WARRANTY);
    }
}
