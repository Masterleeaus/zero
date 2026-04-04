<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Money\Invoice;
use App\Models\Money\QuoteItem;
use App\Models\Premises\Premises;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    public const STATUS_CONVERTED = 'converted';

    /**
     * Valid status values.
     * - accepted: used when turning a quote into a service job
     * - approved/sent: required before conversion to invoice
     * - converted: marks quotes already invoiced
     */
    public const STATUSES = [
        'draft',
        'sent',
        'accepted',
        'rejected',
        'expired',
        'approved',
        self::STATUS_CONVERTED,
    ];

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'enquiry_id',
        'site_id',
        'premises_id',
        'quote_number',
        'title',
        'status',
        'issue_date',
        'valid_until',
        'currency',
        'subtotal',
        'tax',
        'total',
        'notes',
        'checklist_template',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'total'      => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'checklist_template' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function latestInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Physical service delivery location (Odoo: fsm_location_id).
     */
    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }

    // ── Business logic helpers ────────────────────────────────────────────────

    public function recomputeTotalsFromItems(): void
    {
        /** @var Collection<int, QuoteItem> $items */
        $items = $this->items;
        $subtotal = $items->sum(fn (QuoteItem $item) => (float) ($item->quantity * $item->unit_price));
        $tax = $items->sum(function (QuoteItem $item) {
            $line = (float) ($item->quantity * $item->unit_price);
            return $line * ((float) $item->tax_rate) / 100;
        });

        $this->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $subtotal + $tax,
        ]);
    }

    /**
     * Return all quote lines that should generate field-service execution
     * when this quote is accepted (field_service_tracking != 'no').
     *
     * Mirrors Odoo fieldservice_sale: lines with field_service_tracking != 'no'.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, QuoteItem>
     */
    public function toServiceExecutionCandidates(): Collection
    {
        return $this->items()
            ->where('field_service_tracking', '!=', QuoteItem::TRACKING_NONE)
            ->get();
    }

    /**
     * Whether this quote has at least one line that will generate field work.
     */
    public function createsFieldWork(): bool
    {
        return $this->items()
            ->where('field_service_tracking', '!=', QuoteItem::TRACKING_NONE)
            ->exists();
    }

    /**
     * Whether this quote is linked to a service agreement (by agreement_id on
     * any of its resulting service jobs, or by service_agreements.quote_id).
     */
    public function coversAgreementPlan(): bool
    {
        return \App\Models\Work\ServiceAgreement::query()
            ->where('quote_id', $this->id)
            ->orWhere('originating_quote_id', $this->id)
            ->exists();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereIn($query->qualifyColumn('status'), ['accepted', 'approved']);
    }

    // ── fieldservice_sale_recurring_agreement helpers ─────────────────────────

    /**
     * The ServiceAgreement generated from this quote (via quote_id or originating_quote_id).
     *
     * Returns the first/primary agreement created when this quote was approved.
     */
    public function generatedAgreement(): ?\App\Models\Work\ServiceAgreement
    {
        return \App\Models\Work\ServiceAgreement::query()
            ->where(function ($q) {
                $q->where('quote_id', $this->id)
                  ->orWhere('originating_quote_id', $this->id);
            })
            ->where('company_id', $this->company_id)
            ->orderBy('id')
            ->first();
    }

    /**
     * The recurring ServicePlan generated from this quote's sale agreement.
     *
     * Resolves via the generated agreement's originated_from_sale plan.
     */
    public function generatedRecurringPlan(): ?\App\Models\Work\ServicePlan
    {
        $agreement = $this->generatedAgreement();

        if (! $agreement) {
            return null;
        }

        return $agreement->servicePlans()
            ->where('originated_from_sale', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * Summary of the service commitments generated from this quote.
     *
     * @return array{
     *     quote_id: int,
     *     has_field_work: bool,
     *     has_recurring: bool,
     *     agreement_id: int|null,
     *     plan_id: int|null,
     *     committed_visits: int|null,
     *     projected_visits: int,
     *     materialized_jobs: int
     * }
     */
    public function serviceCommitmentSummary(): array
    {
        $agreement = $this->generatedAgreement();
        $plan      = $this->generatedRecurringPlan();

        $projectedVisits   = 0;
        $materializedJobs  = 0;

        if ($plan) {
            $projectedVisits  = $plan->visits()->count();
            $materializedJobs = $plan->visits()->whereNotNull('service_job_id')->count();
        }

        return [
            'quote_id'          => $this->id,
            'has_field_work'    => $this->createsFieldWork(),
            'has_recurring'     => $this->coversAgreementPlan(),
            'agreement_id'      => $agreement?->id,
            'plan_id'           => $plan?->id,
            'committed_visits'  => $agreement?->committed_visits,
            'projected_visits'  => $projectedVisits,
            'materialized_jobs' => $materializedJobs,
        ];
    }
}
