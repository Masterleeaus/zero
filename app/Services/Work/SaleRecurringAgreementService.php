<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\SaleRecurringAgreementCreated;
use App\Events\Work\SaleRecurringAgreementUpdated;
use App\Events\Work\SaleRecurringCoverageApplied;
use App\Events\Work\SaleRecurringPlanGenerated;
use App\Events\Work\SaleRecurringVisitMaterialized;
use App\Events\Work\SaleRecurringVisitProjected;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * SaleRecurringAgreementService
 *
 * Implements the full commercial-to-execution pipeline for
 * fieldservice_sale_recurring_agreement.
 *
 * Extends (does not replace) FieldServiceSaleService to handle the specific
 * case where a quote/sale approval creates a recurring service agreement with
 * committed visit counts and projected service plan visits.
 *
 * Pipeline:
 *   Quote approved
 *     → recurring service lines detected
 *       → ServiceAgreement created/updated (recurring_source=sale, sale_recurrence_terms)
 *         → ServicePlan generated (originated_from_sale=true, commercial_visits_committed)
 *           → ServicePlanVisit records projected (sale_originated=true)
 *             → ServiceJob materialized per visit (on demand or auto)
 *
 * All canonical entities (Quote, ServiceAgreement, ServicePlan, ServicePlanVisit,
 * ServiceJob) are used — no parallel sales-recurring silo is created.
 */
class SaleRecurringAgreementService
{
    public function __construct(
        private readonly FieldServiceSaleService $saleSvc,
    ) {}

    // ── Full pipeline ─────────────────────────────────────────────────────────

    /**
     * Run the full sale-recurring-agreement pipeline for an accepted Quote.
     *
     * Steps:
     *   1. Detect recurring service lines on the quote.
     *   2. Create or update the ServiceAgreement with commercial terms.
     *   3. Generate a ServicePlan with committed visit counts.
     *   4. Project ServicePlanVisit records for the coverage window.
     *
     * @param  array<string, mixed>  $options  {
     *     agreement?: ServiceAgreement,       existing agreement to extend
     *     project_visits?: bool,             default true — project visits at creation
     *     materialize_first_visit?: bool,    default false — dispatch first visit as job
     *     plan_attributes?: array,           overrides for ServicePlan creation
     *     agreement_attributes?: array,      overrides for ServiceAgreement creation
     * }
     * @return array{
     *     agreement: ServiceAgreement,
     *     plan: ServicePlan,
     *     visits: Collection<int, ServicePlanVisit>,
     *     firstJob: ServiceJob|null
     * }
     */
    public function runRecurringPipeline(Quote $quote, array $options = []): array
    {
        $agreement = $options['agreement'] ?? null;
        $projectVisits = $options['project_visits'] ?? true;
        $materializeFirst = $options['materialize_first_visit'] ?? false;

        return DB::transaction(function () use (
            $quote,
            $agreement,
            $projectVisits,
            $materializeFirst,
            $options
        ) {
            // Step 1: Create or update the recurring agreement
            if ($agreement) {
                $agreement = $this->updateRecurringTermsFromSale(
                    $agreement,
                    $quote,
                    $options['agreement_attributes'] ?? [],
                );
            } else {
                $agreement = $this->createRecurringAgreement(
                    $quote,
                    $options['agreement_attributes'] ?? [],
                );
            }

            // Step 2: Generate the recurring service plan
            $plan = $this->attachRecurringPlanToAgreement(
                $quote,
                $agreement,
                $options['plan_attributes'] ?? [],
            );

            // Step 3: Project visits
            $visits = collect();
            if ($projectVisits) {
                $count = (int) ($plan->commercial_visits_committed ?? 12);
                $visits = $this->projectVisits($plan, $count);
            }

            // Step 4: Optionally materialize the first visit as a job
            $firstJob = null;
            if ($materializeFirst && $visits->isNotEmpty()) {
                $firstJob = $this->materializeVisit($visits->first());
            }

            return compact('agreement', 'plan', 'visits', 'firstJob');
        });
    }

    // ── Agreement creation / update ───────────────────────────────────────────

    /**
     * Create a new ServiceAgreement from a quote with recurring commercial terms.
     *
     * Sets recurring_source=sale, encodes sale_recurrence_terms from the quote,
     * and sets commercial_start_date / commercial_end_date / committed_visits.
     *
     * @param  array<string, mixed>  $attributes  Optional overrides.
     */
    public function createRecurringAgreement(
        Quote $quote,
        array $attributes = [],
    ): ServiceAgreement {
        $terms = $this->buildRecurrenceTerms($quote);

        $defaults = [
            'company_id'             => $quote->company_id,
            'customer_id'            => $quote->customer_id,
            'premises_id'            => $quote->premises_id,
            'quote_id'               => $quote->id,
            'originating_quote_id'   => $quote->id,
            'status'                 => 'active',
            'recurring_source'       => 'sale',
            'sale_recurrence_terms'  => $terms,
            'commercial_start_date'  => $terms['start_date'] ?? $quote->issue_date?->toDateString() ?? now()->toDateString(),
            'commercial_end_date'    => $terms['end_date'] ?? $quote->valid_until?->toDateString(),
            'committed_visits'       => $terms['visits_per_cycle'] ?? null,
            'covered_visits_used'    => 0,
            'frequency'              => $terms['frequency'] ?? 'monthly',
            'next_run_at'            => Carbon::parse($terms['start_date'] ?? now()),
        ];

        $agreement = ServiceAgreement::create(array_merge($defaults, $attributes));

        SaleRecurringAgreementCreated::dispatch($quote, $agreement);

        return $agreement;
    }

    /**
     * Update the recurring commercial terms on an existing ServiceAgreement
     * from a renewal or extension quote.
     *
     * Updates: sale_recurrence_terms, commercial_end_date, committed_visits,
     * renewal_quote_id, and (if status != active) activates the agreement.
     *
     * @param  array<string, mixed>  $attributes  Optional overrides.
     */
    public function updateRecurringTermsFromSale(
        ServiceAgreement $agreement,
        Quote $quote,
        array $attributes = [],
    ): ServiceAgreement {
        $terms = $this->buildRecurrenceTerms($quote);

        $updates = array_merge([
            'renewal_quote_id'      => $quote->id,
            'sale_recurrence_terms' => $terms,
            'commercial_end_date'   => $terms['end_date'] ?? $quote->valid_until?->toDateString() ?? $agreement->commercial_end_date,
            'committed_visits'      => ($agreement->committed_visits ?? 0) + ($terms['visits_per_cycle'] ?? 0),
            'recurring_source'      => 'sale',
        ], $attributes);

        if (! $agreement->isActive()) {
            $updates['status'] = 'active';
        }

        $agreement->update($updates);

        SaleRecurringAgreementUpdated::dispatch($agreement->fresh(), $quote);

        return $agreement->fresh();
    }

    // ── Service plan generation ───────────────────────────────────────────────

    /**
     * Create a recurring ServicePlan from a quote and its sale agreement.
     *
     * Sets originated_from_sale=true, sale_recurring_type, commercial_visits_committed,
     * commercial_start_date, commercial_end_date, and sale_agreement_id.
     *
     * @param  array<string, mixed>  $planAttributes  Optional overrides.
     */
    public function attachRecurringPlanToAgreement(
        Quote $quote,
        ServiceAgreement $agreement,
        array $planAttributes = [],
    ): ServicePlan {
        $terms = $agreement->sale_recurrence_terms ?? $this->buildRecurrenceTerms($quote);

        $defaults = [
            'company_id'                  => $quote->company_id,
            'created_by'                  => $quote->created_by,
            'customer_id'                 => $quote->customer_id,
            'agreement_id'                => $agreement->id,
            'premises_id'                 => $quote->premises_id,
            'name'                        => $quote->title ?? 'Recurring Service Plan',
            'status'                      => 'active',
            'is_active'                   => true,
            'originated_from_sale'        => true,
            'sale_recurring_type'         => $terms['recurring_type'] ?? 'committed',
            'commercial_visits_committed' => $terms['visits_per_cycle'] ?? null,
            'commercial_start_date'       => $terms['start_date'] ?? $agreement->commercial_start_date ?? $quote->issue_date?->toDateString() ?? now()->toDateString(),
            'commercial_end_date'         => $terms['end_date'] ?? $agreement->commercial_end_date ?? $quote->valid_until?->toDateString(),
            'sale_agreement_id'           => $agreement->id,
            'frequency'                   => $terms['frequency'] ?? $agreement->frequency ?? 'monthly',
            'interval'                    => $terms['interval'] ?? 1,
            'starts_on'                   => $terms['start_date'] ?? $quote->issue_date?->toDateString() ?? now()->toDateString(),
            'next_visit_due'              => $terms['start_date'] ?? $quote->issue_date?->toDateString() ?? now()->toDateString(),
        ];

        $plan = ServicePlan::create(array_merge($defaults, $planAttributes));

        SaleRecurringPlanGenerated::dispatch($quote, $agreement, $plan);
        SaleRecurringCoverageApplied::dispatch($agreement, $plan);

        return $plan;
    }

    // ── Visit projection ──────────────────────────────────────────────────────

    /**
     * Project $count future ServicePlanVisit records for a sale-originated plan.
     *
     * Projections are pending visits (not yet materialized as ServiceJobs).
     * The plan's next_visit_due date is used as the start of the series.
     *
     * @return Collection<int, ServicePlanVisit>
     */
    public function projectVisits(ServicePlan $plan, int $count = 12): Collection
    {
        $visits = collect();

        $current = $plan->next_visit_due
            ? Carbon::parse($plan->next_visit_due)
            : Carbon::now();

        $end = $plan->commercial_end_date
            ? Carbon::parse($plan->commercial_end_date)
            : null;

        $created = 0;
        while ($created < $count) {
            // Stop if we have exceeded the commercial end date
            if ($end && $current->gt($end)) {
                break;
            }

            $visit = ServicePlanVisit::create([
                'company_id'       => $plan->company_id,
                'created_by'       => $plan->created_by,
                'service_plan_id'  => $plan->id,
                'visit_type'       => 'recurring',
                'scheduled_for'    => $current->copy()->startOfDay(),
                'scheduled_date'   => $current->toDateString(),
                'status'           => 'pending',
                'sale_originated'  => true,
                'sale_agreement_id' => $plan->sale_agreement_id ?? $plan->agreement_id,
            ]);

            SaleRecurringVisitProjected::dispatch($visit, $plan);

            $visits->push($visit);
            $created++;

            // Advance to the next scheduled date
            $current = $this->advanceDate($current, $plan->frequency, (int) ($plan->interval ?? 1));
        }

        return $visits;
    }

    // ── Visit materialization ─────────────────────────────────────────────────

    /**
     * Materialize a projected sale-recurring visit as a ServiceJob.
     *
     * Calls the existing ServicePlanVisit::dispatch() which handles the
     * canonical job creation and ServicePlanVisitDispatched event.
     * Additionally fires SaleRecurringVisitMaterialized.
     *
     * @param  array<string, mixed>  $jobAttributes  Optional overrides for the job.
     */
    public function materializeVisit(
        ServicePlanVisit $visit,
        array $jobAttributes = [],
    ): ServiceJob {
        // Reuse canonical dispatch logic
        $job = $visit->dispatch($jobAttributes);

        SaleRecurringVisitMaterialized::dispatch($visit->fresh(), $job);

        return $job;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Extract recurring service lines from a quote.
     *
     * A "recurring" line is one with field_service_tracking != 'no'
     * and service_tracking_type set to a recurring pattern.
     *
     * @return Collection<int, QuoteItem>
     */
    public function extractRecurringLines(Quote $quote): Collection
    {
        return $quote->items()
            ->where('field_service_tracking', '!=', QuoteItem::TRACKING_NONE)
            ->whereNotNull('service_tracking_type')
            ->get();
    }

    /**
     * Whether a quote has any recurring service lines.
     */
    public function hasRecurringLines(Quote $quote): bool
    {
        return $this->extractRecurringLines($quote)->isNotEmpty();
    }

    /**
     * Build the recurrence terms array from a quote.
     *
     * Reads frequency, interval, and visit counts from the first recurring line
     * or falls back to sensible defaults.
     *
     * @return array<string, mixed>
     */
    protected function buildRecurrenceTerms(Quote $quote): array
    {
        $line = $this->extractRecurringLines($quote)->first();

        return [
            'frequency'      => $line?->service_tracking_type ?? 'monthly',
            'interval'       => 1,
            'recurring_type' => 'committed',
            'visits_per_cycle' => $line ? (int) ($line->quantity ?? 1) : 1,
            'start_date'     => $quote->issue_date?->toDateString() ?? now()->toDateString(),
            'end_date'       => $quote->valid_until?->toDateString(),
        ];
    }

    /**
     * Advance a Carbon date by one period given a frequency and interval.
     */
    protected function advanceDate(Carbon $date, ?string $frequency, int $interval = 1): Carbon
    {
        return match ($frequency) {
            'daily'       => $date->addDays($interval),
            'weekly'      => $date->addWeeks($interval),
            'fortnightly' => $date->addWeeks(2 * $interval),
            'monthly'     => $date->addMonthsNoOverflow($interval),
            'quarterly'   => $date->addMonthsNoOverflow(3 * $interval),
            'annual'      => $date->addYears($interval),
            default       => $date->addMonthsNoOverflow($interval),
        };
    }
}
