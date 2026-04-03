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
}
