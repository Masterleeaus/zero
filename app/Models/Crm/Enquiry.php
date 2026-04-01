<?php

declare(strict_types=1);

namespace App\Models\Crm;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Money\Quote;
use App\Models\User;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquiry extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'name',
        'email',
        'phone',
        'status',
        'source',
        'notes',
        'team_id',
        'quote_id',
        'follow_up_at',
        'follow_up_note',
        'follow_up_done',
    ];

    protected $attributes = [
        'status'         => 'open',
        'follow_up_done' => false,
    ];

    protected $casts = [
        'follow_up_at'   => 'datetime',
        'follow_up_done' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Service jobs created from this enquiry (CRM lead).
     *
     * Module 6 (fieldservice_crm) — lead → service-job linkage.
     */
    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'enquiry_id');
    }

    // ── CRM Timeline Service Visibility ──────────────────────────────────────

    /**
     * Most recently completed service job linked to this enquiry.
     */
    public function latestServiceJob(): ?ServiceJob
    {
        return $this->serviceJobs()
            ->where('status', 'completed')
            ->latest('date_end')
            ->first();
    }

    public function scopeDueFollowUps(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId)
            ->where('follow_up_at', '<=', now())
            ->where('follow_up_done', false)
            ->whereNotNull('follow_up_at');
    }
}
