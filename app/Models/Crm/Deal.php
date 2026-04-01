<?php

declare(strict_types=1);

namespace App\Models\Crm;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'title',
        'value',
        'currency',
        'status',
        'stage',
        'expected_close_date',
        'notes',
    ];

    protected $attributes = [
        'status' => 'open',
        'currency' => 'AUD',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DealNote::class);
    }

    /**
     * Service jobs created from this deal (CRM opportunity).
     *
     * Module 6 (fieldservice_crm) — opportunity → service-job linkage.
     */
    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'deal_id');
    }

    // ── CRM Timeline Service Visibility ──────────────────────────────────────

    /**
     * Most recently completed service job linked to this deal.
     */
    public function latestServiceJob(): ?ServiceJob
    {
        return $this->serviceJobs()
            ->where('status', 'completed')
            ->latest('date_end')
            ->first();
    }

    /**
     * All open (non-completed, non-cancelled) service jobs for this deal.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function openServiceJobs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceJobs()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('scheduled_date_start')
            ->get();
    }
}
