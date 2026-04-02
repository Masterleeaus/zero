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
 * ServicePlanVisit — an individual scheduled occurrence within a ServicePlan.
 *
 * When dispatched, the visit is linked to a ServiceJob that executes the work.
 *
 *   ServicePlan → ServicePlanVisit → ServiceJob
 *
 * Status: pending | scheduled | completed | skipped | cancelled
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
        'scheduled_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'pending',
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Dispatch this visit as a ServiceJob.
     *
     * Creates the job if not already linked, and marks this visit as scheduled.
     */
    public function dispatch(array $jobAttributes = []): ServiceJob
    {
        if ($this->service_job_id && $this->serviceJob) {
            return $this->serviceJob;
        }

        $plan      = $this->plan;
        $agreement = $plan?->agreement;

        $data = array_merge([
            'company_id'           => $this->company_id,
            'customer_id'          => $agreement?->customer_id,
            'premises_id'          => $plan?->premises_id ?? $agreement?->premises_id,
            'agreement_id'         => $agreement?->id,
            'title'                => $plan?->title ?? 'Scheduled visit',
            'status'               => 'scheduled',
            'scheduled_date_start' => $this->scheduled_date,
        ], $jobAttributes);

        $job = ServiceJob::create($data);

        $this->update([
            'service_job_id' => $job->id,
            'status'         => 'scheduled',
        ]);

        return $job;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '>=', now()->toDateString());
    }
}
