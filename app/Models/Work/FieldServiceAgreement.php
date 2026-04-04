<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FieldServiceAgreement — contract-driven service lifecycle entity.
 *
 * Merges Odoo fieldservice_sale_agreement into the Titan Work domain.
 *
 * Links a Quote-originated commitment to recurring service execution:
 *   Quote → FieldServiceAgreement → ServicePlanVisits → ServiceJobs → Invoices
 *
 * billing_cycle: monthly | quarterly | annually | one_off
 * service_frequency: weekly | fortnightly | monthly | quarterly | custom
 * status: draft | active | suspended | expired | cancelled | renewed
 */
class FieldServiceAgreement extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'field_service_agreements';

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'premises_id',
        'quote_id',
        'title',
        'reference',
        'start_date',
        'end_date',
        'billing_cycle',
        'service_frequency',
        'status',
        'terms_json',
        'auto_generate_jobs',
        'auto_generate_visits',
        'notes',
    ];

    protected $casts = [
        'start_date'           => 'date',
        'end_date'             => 'date',
        'terms_json'           => 'array',
        'auto_generate_jobs'   => 'boolean',
        'auto_generate_visits' => 'boolean',
    ];

    protected $attributes = [
        'status'               => 'draft',
        'auto_generate_jobs'   => false,
        'auto_generate_visits' => false,
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, int $withinDays = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays($withinDays)->toDateString())
            ->where('end_date', '>=', now()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('end_date')
                    ->where('end_date', '<', now()->toDateString())
                    ->where('status', 'active');
            });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Service jobs that originate from this FieldServiceAgreement recurring plan.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'recurring_source_id');
    }

    /**
     * Service plan visits linked to this agreement.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(ServicePlanVisit::class, 'field_service_agreement_id');
    }

    /**
     * Quote lines (sale order lines) tied to this agreement.
     */
    public function saleLines(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'field_service_agreement_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->end_date !== null && $this->end_date->isPast();
    }

    public function isExpiring(int $withinDays = 30): bool
    {
        if (! $this->isActive() || $this->end_date === null) {
            return false;
        }

        return $this->end_date->between(now(), now()->addDays($withinDays));
    }

    /**
     * Execution summary: jobs + visits counts for this agreement.
     *
     * @return array{agreement_id: int, total_jobs: int, completed_jobs: int, total_visits: int, completed_visits: int}
     */
    public function executionSummary(): array
    {
        return [
            'agreement_id'    => $this->id,
            'total_jobs'      => $this->jobs()->count(),
            'completed_jobs'  => $this->jobs()->where('status', 'completed')->count(),
            'total_visits'    => $this->visits()->count(),
            'completed_visits' => $this->visits()->where('status', 'completed')->count(),
        ];
    }
}
