<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceAgreement extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $guarded = [];

    protected $casts = [
        'next_run_at' => 'datetime',
        'expired_at'  => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scheduleNext(): void
    {
        if ($this->frequency && $this->next_run_at) {
            $this->next_run_at = $this->next_run_at->add($this->frequencyInterval());
            $this->save();
        }
    }

    public function createJob(array $attributes = []): ServiceJob
    {
        $data = array_merge([
            'company_id'   => $this->company_id,
            'customer_id'  => $this->customer_id,
            'site_id'      => $this->site_id,
            'agreement_id' => $this->id,
            'title'        => $attributes['title'] ?? 'Recurring service',
            'status'       => $attributes['status'] ?? 'scheduled',
            'scheduled_at' => $attributes['scheduled_at'] ?? $this->next_run_at,
        ], $attributes);

        return $this->jobs()->create($data);
    }

    protected function frequencyInterval(): \DateInterval
    {
        return match ($this->frequency) {
            'weekly' => new \DateInterval('P7D'),
            'monthly' => new \DateInterval('P1M'),
            'quarterly' => new \DateInterval('P3M'),
            default => new \DateInterval('P1M'),
        };
    }

    public function scopeNotPaused($query)
    {
        return $query->where('status', '!=', 'paused');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'agreement_id');
    }

    public function servicePlan(): HasOne
    {
        return $this->hasOne(ServicePlan::class, 'agreement_id');
    }

    public function servicePlans(): HasMany
    {
        return $this->hasMany(ServicePlan::class, 'agreement_id');
    }

    /**
     * All service plan visits linked to this agreement via its service plans.
     */
    public function visits(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(ServicePlanVisit::class, ServicePlan::class, 'agreement_id', 'service_plan_id');
    }

    /**
     * Whether this agreement is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ── fieldservice_sale_agreement helpers ───────────────────────────────────

    /**
     * The Quote that activated or originated this service agreement.
     *
     * Mirrors Odoo fieldservice_sale_agreement: agreement_id propagated from sale → fsm.order.
     * Checks originating_quote_id first, then quote_id.
     */
    public function originatingSale(): ?\App\Models\Money\Quote
    {
        if ($this->originating_quote_id) {
            return \App\Models\Money\Quote::find($this->originating_quote_id);
        }

        return $this->quote;
    }

    /**
     * Summary of service coverage sold through this agreement.
     *
     * @return array{
     *     agreement_id: int,
     *     status: string,
     *     originating_quote_id: int|null,
     *     total_jobs: int,
     *     completed_jobs: int,
     *     pending_jobs: int,
     *     total_visits: int,
     *     completed_visits: int
     * }
     */
    public function saleCoverageSummary(): array
    {
        $jobs = $this->jobs();

        return [
            'agreement_id'        => $this->id,
            'status'              => $this->status,
            'originating_quote_id' => $this->originating_quote_id ?? $this->quote_id,
            'total_jobs'          => (clone $jobs)->count(),
            'completed_jobs'      => (clone $jobs)->where('status', 'completed')->count(),
            'pending_jobs'        => (clone $jobs)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_visits'        => $this->visits()->count(),
            'completed_visits'    => $this->visits()->where('status', 'completed')->count(),
        ];
    }
}
