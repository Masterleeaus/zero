<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Premises\Premises;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recurring service configuration for a Premises / Customer.
 *
 * Position in the triangle:
 *   Agreement   → defines entitlement / commercial terms
 *   ServicePlan → defines visit schedule & template injections
 *   ServiceJob  → executes work on each scheduled visit
 *
 * Frequency values: daily | weekly | fortnightly | monthly | quarterly | annual
 * Status values:    active | paused | completed | cancelled
 */
class ServicePlan extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'service_plans';

    protected $fillable = [
        'company_id',
        'created_by',
        'premises_id',
        'customer_id',
        'agreement_id',
        'origin_quote_id',
        'recurring_product_ref',
        'recurrence_type',
        'auto_generate_visits',
        'equipment_scope',
        'name',
        'title',
        'service_type',
        'frequency',
        'interval',
        'visits_per_cycle',
        'rrule',
        'preferred_days',
        'preferred_times',
        'starts_on',
        'start_date',
        'ends_on',
        'end_date',
        'next_visit_due',
        'last_visit_completed',
        'is_active',
        'status',
        'notes',
        // fieldservice_sale_recurring_agreement
        'originated_from_sale',
        'sale_recurring_type',
        'commercial_visits_committed',
        'commercial_start_date',
        'commercial_end_date',
        'sale_agreement_id',
    ];

    protected $casts = [
        'preferred_days'              => 'array',
        'preferred_times'             => 'array',
        'starts_on'                   => 'date',
        'start_date'                  => 'date',
        'ends_on'                     => 'date',
        'end_date'                    => 'date',
        'next_visit_due'              => 'date',
        'last_visit_completed'        => 'date',
        'is_active'                   => 'boolean',
        'interval'                    => 'integer',
        'visits_per_cycle'            => 'integer',
        'auto_generate_visits'        => 'boolean',
        'equipment_scope'             => 'array',
        // fieldservice_sale_recurring_agreement
        'originated_from_sale'        => 'boolean',
        'commercial_visits_committed' => 'integer',
        'commercial_start_date'       => 'date',
        'commercial_end_date'         => 'date',
    ];

    protected $attributes = [
        'frequency'            => 'monthly',
        'interval'             => 1,
        'is_active'            => true,
        'status'               => 'active',
        'visits_per_cycle'     => 1,
        'auto_generate_visits' => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    /**
     * The originating sale quote that generated this plan.
     */
    public function originatingQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'origin_quote_id');
    }

    /**
     * The sale agreement that backs this plan's recurring commercial terms.
     */
    public function saleAgreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'sale_agreement_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ServicePlanVisit::class, 'service_plan_id')
            ->orderBy('scheduled_for');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(ServicePlanChecklist::class, 'service_plan_id')
            ->orderBy('sort_order');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('next_visit_due', '<=', now()->toDateString());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Advance the next_visit_due date after a visit is completed.
     */
    public function advanceNextVisitDue(): void
    {
        if (! $this->next_visit_due) {
            return;
        }

        $date = \Carbon\Carbon::parse($this->next_visit_due);

        $this->next_visit_due = match ($this->frequency) {
            'daily'       => $date->addDays($this->interval)->toDateString(),
            'weekly'      => $date->addWeeks($this->interval)->toDateString(),
            'fortnightly' => $date->addWeeks(2 * $this->interval)->toDateString(),
            'monthly'     => $date->addMonthsNoOverflow($this->interval)->toDateString(),
            'quarterly'   => $date->addMonthsNoOverflow(3 * $this->interval)->toDateString(),
            'annual'      => $date->addYears($this->interval)->toDateString(),
            default       => $date->addMonthsNoOverflow($this->interval)->toDateString(),
        };

        $this->last_visit_completed = now()->toDateString();
        $this->save();
    }

    // ── fieldservice_sale_recurring helpers ──────────────────────────────────

    /**
     * The Quote that triggered this recurring plan's generation.
     *
     * Resolves via origin_quote_id first, then via the linked agreement.
     */
    public function originatingSale(): ?\App\Models\Money\Quote
    {
        if ($this->origin_quote_id) {
            return \App\Models\Money\Quote::find($this->origin_quote_id);
        }

        $agreement = $this->agreement;

        if (! $agreement) {
            return null;
        }

        return $agreement->originatingSale();
    }

    /**
     * InstalledEquipment records within this plan's equipment scope.
     *
     * Reads the json `equipment_scope` column.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Equipment\InstalledEquipment>
     */
    public function coverageEquipment(): \Illuminate\Database\Eloquent\Collection
    {
        $scope = $this->equipment_scope;

        if (empty($scope)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return \App\Models\Equipment\InstalledEquipment::query()
            ->whereIn('id', $scope)
            ->where('company_id', $this->company_id)
            ->get();
    }

    /**
     * Summary of recurring coverage scope for this plan.
     *
     * @return array{
     *     plan_id: int,
     *     recurrence_type: string|null,
     *     frequency: string,
     *     auto_generate_visits: bool,
     *     equipment_count: int,
     *     pending_visits: int,
     *     completed_visits: int,
     *     next_visit_due: string|null
     * }
     */
    public function recurringCoverageScope(): array
    {
        return [
            'plan_id'              => $this->id,
            'recurrence_type'      => $this->recurrence_type,
            'frequency'            => $this->frequency,
            'auto_generate_visits' => (bool) $this->auto_generate_visits,
            'equipment_count'      => count($this->equipment_scope ?? []),
            'pending_visits'       => $this->visits()->whereIn('status', ['pending', 'scheduled'])->count(),
            'completed_visits'     => $this->visits()->where('status', 'completed')->count(),
            'next_visit_due'       => $this->next_visit_due?->toDateString(),
        ];
    }

    // ── fieldservice_sale_recurring_agreement helpers ─────────────────────────

    /**
     * The ServiceAgreement that funded this recurring plan commercially.
     *
     * Checks sale_agreement_id first (explicit sale link), then agreement_id.
     */
    public function originatingSaleAgreement(): ?\App\Models\Work\ServiceAgreement
    {
        if ($this->sale_agreement_id) {
            return \App\Models\Work\ServiceAgreement::find($this->sale_agreement_id);
        }

        return $this->agreement;
    }

    /**
     * Summary of the commercial origin of this service plan.
     *
     * @return array{
     *     plan_id: int,
     *     originated_from_sale: bool,
     *     sale_recurring_type: string|null,
     *     commercial_visits_committed: int|null,
     *     commercial_start_date: string|null,
     *     commercial_end_date: string|null,
     *     agreement_id: int|null,
     *     originating_quote_id: int|null
     * }
     */
    public function commercialOriginSummary(): array
    {
        $saleAgreement = $this->originatingSaleAgreement();

        return [
            'plan_id'                     => $this->id,
            'originated_from_sale'        => (bool) $this->originated_from_sale,
            'sale_recurring_type'         => $this->sale_recurring_type,
            'commercial_visits_committed' => $this->commercial_visits_committed,
            'commercial_start_date'       => $this->commercial_start_date?->toDateString(),
            'commercial_end_date'         => $this->commercial_end_date?->toDateString(),
            'agreement_id'                => $saleAgreement?->id,
            'originating_quote_id'        => $saleAgreement?->originating_quote_id ?? $saleAgreement?->quote_id,
        ];
    }
}
