<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSLABreach extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'contract_sla_breaches';

    protected $guarded = [];

    protected $casts = [
        'sla_hours'    => 'integer',
        'actual_hours' => 'float',
        'breached_at'  => 'datetime',
        'notified_at'  => 'datetime',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'job_id');
    }

    /**
     * Whether a notification has been sent for this breach.
     */
    public function isNotified(): bool
    {
        return $this->notified_at !== null;
    }

    /**
     * How many hours over SLA the breach is.
     */
    public function overrunHours(): float
    {
        return max(0.0, $this->actual_hours - $this->sla_hours);
    }
}
