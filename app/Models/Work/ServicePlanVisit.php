<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An individual planned visit within a ServicePlan.
 *
 * Can be converted into a ServiceJob via generateJob().
 *
 * Visit types: service | inspection | maintenance | key_handover
 * Status:      scheduled | confirmed | in_progress | completed | cancelled | no_access
 *
 * Source: ManagedPremises/Entities/PropertyVisit.php.
 */
class ServicePlanVisit extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'service_plan_visits';

    protected $fillable = [
        'company_id',
        'created_by',
        'service_plan_id',
        'service_job_id',
        'visit_type',
        'scheduled_for',
        'assigned_to',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'completed_at'  => 'datetime',
    ];

    protected $attributes = [
        'status' => 'scheduled',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasLinkedJob(): bool
    {
        return $this->service_job_id !== null;
    }

    /**
     * Generate a ServiceJob from this planned visit.
     *
     * Pulls context from the parent ServicePlan (premises, customer, agreement).
     */
    public function generateJob(array $attributes = []): ServiceJob
    {
        $plan = $this->plan;

        $data = array_merge([
            'company_id'   => $this->company_id,
            'customer_id'  => $plan->customer_id,
            'premises_id'  => $plan->premises_id,
            'agreement_id' => $plan->agreement_id,
            'title'        => $attributes['title'] ?? ($plan->name . ' visit'),
            'status'       => $attributes['status'] ?? 'scheduled',
            'scheduled_at' => $this->scheduled_for,
        ], $attributes);

        $job = ServiceJob::create($data);

        $this->service_job_id = $job->id;
        $this->save();

        return $job;
    }
}
