<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Premises\Building;
use App\Models\Premises\Floor;
use App\Models\Premises\Premises;
use App\Models\Premises\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @deprecated Use App\Models\Inspection\InspectionInstance (canonical model for the
 *             inspection_instances table). This class in App\Models\Work\ is a duplicate
 *             and will be removed in a future cleanup pass. All new references should use
 *             the Inspection namespace model.
 *
 * InspectionInstance — an executed inspection linked to a job and/or premises.
 *
 * Attaches to:
 *   - Premises / Building / Floor / Unit (location context)
 *   - ServiceJob (execution context)
 *   - SiteAsset (optional — for asset-specific inspections)
 *
 * One inspection can have many ChecklistRun records.
 *
 * Status: pending | in_progress | completed | failed | cancelled
 * Type:   routine | compliance | handover | safety | quality
 */
class InspectionInstance extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'inspection_instances';

    protected $fillable = [
        'company_id',
        'created_by',
        'service_job_id',
        'premises_id',
        'building_id',
        'floor_id',
        'unit_id',
        'site_asset_id',
        'inspection_type',
        'status',
        'title',
        'notes',
        'fail_reason',
        'assigned_to',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'completed_at'  => 'datetime',
    ];

    protected $attributes = [
        'status'          => 'pending',
        'inspection_type' => 'routine',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function siteAsset(): BelongsTo
    {
        return $this->belongsTo(SiteAsset::class, 'site_asset_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function checklistRuns(): HasMany
    {
        return $this->hasMany(ChecklistRun::class, 'inspection_instance_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPassed(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('service_job_id', $jobId);
    }

    public function scopeForPremises(Builder $query, int $premisesId): Builder
    {
        return $query->where('premises_id', $premisesId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
