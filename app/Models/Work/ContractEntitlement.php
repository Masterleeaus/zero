<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractEntitlement extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'contract_entitlements';

    protected $guarded = [];

    protected $casts = [
        'max_visits'   => 'integer',
        'visits_used'  => 'integer',
        'max_hours'    => 'float',
        'hours_used'   => 'float',
        'resets_on'    => 'date',
        'is_unlimited' => 'boolean',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    /**
     * Whether the visit entitlement for this period is exhausted.
     */
    public function isVisitExhausted(): bool
    {
        if ($this->is_unlimited) {
            return false;
        }

        if ($this->max_visits === null) {
            return false;
        }

        return $this->visits_used >= $this->max_visits;
    }

    /**
     * Whether the hours entitlement for this period is exhausted.
     */
    public function isHoursExhausted(): bool
    {
        if ($this->is_unlimited) {
            return false;
        }

        if ($this->max_hours === null) {
            return false;
        }

        return $this->hours_used >= $this->max_hours;
    }

    /**
     * Remaining visits for this period.
     */
    public function remainingVisits(): ?int
    {
        if ($this->is_unlimited || $this->max_visits === null) {
            return null;
        }

        return max(0, $this->max_visits - $this->visits_used);
    }

    /**
     * Remaining hours for this period.
     */
    public function remainingHours(): ?float
    {
        if ($this->is_unlimited || $this->max_hours === null) {
            return null;
        }

        return max(0.0, $this->max_hours - $this->hours_used);
    }

    /**
     * Whether this entitlement period should be reset.
     */
    public function isDueForReset(): bool
    {
        if ($this->resets_on === null || $this->period_type === 'contract') {
            return false;
        }

        return now()->gte($this->resets_on);
    }
}
