<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\RecurringPlanGenerated;
use App\Events\Work\RecurringPlanUpdated;
use App\Events\Work\RecurringSaleCreated;
use App\Events\Work\RecurringVisitMaterialized;
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
 * RecurringSaleService
 *
 * Implements the recurring sale → service lifecycle pipeline.
 *
 * Mirrors Odoo behaviours from:
 *   - fieldservice_sale_recurring : recurring service products on sale orders
 *
 * Responsibilities:
 *   A) Detect recurring service lines in an accepted Quote
 *   B) Generate a ServicePlan from a recurring sale Quote
 *   C) Generate ServicePlanVisit records for a given plan + date range
 *   D) Materialize a pending ServicePlanVisit into a concrete ServiceJob
 *   E) Regenerate visits when agreement or plan is updated
 *   F) Full recurring pipeline: sale approved → plan → visits
 */
class RecurringSaleService
{
    // Recurrence types mapped from common product categories / service types
    public const RECURRENCE_MAINTENANCE  = 'maintenance';
    public const RECURRENCE_INSPECTION   = 'inspection';
    public const RECURRENCE_COMPLIANCE   = 'compliance';
    public const RECURRENCE_CONTRACT     = 'contract';

    // ── A: Detect recurring lines ────────────────────────────────────────────

    /**
     * Return quote items that represent recurring service products.
     *
     * A line is considered recurring when field_service_tracking is set to
     * 'sale' or 'line' AND the service_tracking_type suggests recurrence
     * (or explicitly contains 'recurring').
     *
     * @return Collection<int, QuoteItem>
     */
    public function recurringLinesFromQuote(Quote $quote): Collection
    {
        return $quote->items()
            ->whereIn('field_service_tracking', [QuoteItem::TRACKING_SALE, QuoteItem::TRACKING_LINE])
            ->where(static function ($q) {
                $q->whereNotNull('service_tracking_type')
                    ->orWhere('field_service_tracking', '!=', 'no');
            })
            ->get()
            ->filter(static function (QuoteItem $item) {
                // Filter to items that indicate a recurring service
                $type = strtolower((string) ($item->service_tracking_type ?? ''));

                return str_contains($type, 'recurring')
                    || str_contains($type, 'maintenance')
                    || str_contains($type, 'inspection')
                    || str_contains($type, 'contract')
                    || str_contains($type, 'plan')
                    || str_contains($type, 'schedule');
            });
    }

    // ── B: Generate service plan from recurring sale ─────────────────────────

    /**
     * Create a ServicePlan from an accepted Quote, representing a recurring service.
     *
     * Mirrors Odoo fieldservice_sale_recurring:
     *   sale order confirmation → service plan generation.
     *
     * Fires RecurringSaleCreated + RecurringPlanGenerated.
     *
     * @param  array<string, mixed>  $planAttributes  Optional overrides.
     */
    public function createRecurringPlanFromSale(
        Quote $quote,
        ServiceAgreement $agreement,
        array $planAttributes = [],
    ): ServicePlan {
        $plan = DB::transaction(function () use ($quote, $agreement, $planAttributes) {
            $defaults = [
                'company_id'           => $quote->company_id,
                'created_by'           => $quote->created_by,
                'customer_id'          => $quote->customer_id,
                'premises_id'          => $quote->premises_id,
                'agreement_id'         => $agreement->id,
                'origin_quote_id'      => $quote->id,
                'name'                 => $planAttributes['name'] ?? ($quote->title ?? 'Recurring Service Plan'),
                'recurrence_type'      => $planAttributes['recurrence_type'] ?? self::RECURRENCE_CONTRACT,
                'frequency'            => $planAttributes['frequency'] ?? 'monthly',
                'auto_generate_visits' => true,
                'status'               => 'active',
                'is_active'            => true,
                'starts_on'            => $planAttributes['starts_on'] ?? ($quote->issue_date ?? now()->toDateString()),
                'next_visit_due'       => $planAttributes['next_visit_due']
                    ?? ($quote->issue_date ? $quote->issue_date->toDateString() : now()->toDateString()),
            ];

            $plan = ServicePlan::create(array_merge($defaults, $planAttributes));

            // Increment cached plan count on agreement
            $agreement->increment('recurring_plan_count');

            return $plan;
        });

        RecurringSaleCreated::dispatch($quote);
        RecurringPlanGenerated::dispatch($plan, $quote);

        return $plan;
    }

    /**
     * Generate recurring plans for each distinct recurring service line on a Quote.
     *
     * One plan per unique recurrence type / service type combination.
     *
     * @return Collection<int, ServicePlan>
     */
    public function createPlansFromRecurringLines(
        Quote $quote,
        ServiceAgreement $agreement,
    ): Collection {
        $lines = $this->recurringLinesFromQuote($quote);

        if ($lines->isEmpty()) {
            return collect();
        }

        $plans = collect();

        DB::transaction(function () use ($quote, $agreement, $lines, &$plans) {
            // Group lines by their service_tracking_type to avoid duplicating plans
            $grouped = $lines->groupBy('service_tracking_type');

            foreach ($grouped as $trackingType => $groupLines) {
                $recurrenceType = $this->mapTrackingTypeToRecurrenceType((string) $trackingType);
                $firstLine      = $groupLines->first();

                $plan = $this->createRecurringPlanFromSale($quote, $agreement, [
                    'name'            => $firstLine->description ?? ('Recurring ' . ucfirst($recurrenceType)),
                    'recurrence_type' => $recurrenceType,
                ]);

                $plans->push($plan);
            }
        });

        return $plans;
    }

    // ── C: Generate visits for a plan ────────────────────────────────────────

    /**
     * Generate ServicePlanVisit records for a plan within a date range.
     *
     * Respects the plan's frequency and does not create duplicate visits
     * for dates that already have a pending/scheduled visit.
     *
     * @param  Carbon|null  $from  Start of range (defaults to plan's next_visit_due or starts_on)
     * @param  Carbon|null  $until End of range (defaults to 3 months out)
     * @return Collection<int, ServicePlanVisit>
     */
    public function generateVisitsForPlan(
        ServicePlan $plan,
        ?Carbon $from = null,
        ?Carbon $until = null,
    ): Collection {
        $from  ??= Carbon::parse($plan->next_visit_due ?? $plan->starts_on ?? $plan->start_date ?? now());
        $until ??= $from->copy()->addMonths(3);

        $created = collect();

        DB::transaction(function () use ($plan, $from, $until, &$created) {
            $current = $from->copy();

            while ($current->lte($until)) {
                $dateStr = $current->toDateString();

                // Skip if a visit already exists for this date
                $exists = ServicePlanVisit::query()
                    ->where('service_plan_id', $plan->id)
                    ->whereDate('scheduled_date', $dateStr)
                    ->whereIn('status', ['pending', 'scheduled'])
                    ->exists();

                if (! $exists) {
                    $visit = ServicePlanVisit::create([
                        'company_id'      => $plan->company_id,
                        'created_by'      => $plan->created_by,
                        'service_plan_id' => $plan->id,
                        'visit_type'      => $this->visitTypeFromRecurrenceType($plan->recurrence_type),
                        'scheduled_date'  => $dateStr,
                        'scheduled_for'   => $current->startOfDay(),
                        'status'          => 'pending',
                        'coverage_source' => 'agreement',
                    ]);

                    $created->push($visit);
                }

                $current = $this->advanceDate($current, $plan->frequency, $plan->interval ?? 1);
            }
        });

        return $created;
    }

    // ── D: Materialize visit to job ──────────────────────────────────────────

    /**
     * Materialize a pending ServicePlanVisit into a concrete ServiceJob.
     *
     * Fires RecurringVisitMaterialized.
     *
     * @param  array<string, mixed>  $jobAttributes  Optional overrides for the created job.
     */
    public function materializeVisitToJob(
        ServicePlanVisit $visit,
        array $jobAttributes = [],
    ): ServiceJob {
        if ($visit->service_job_id && $visit->serviceJob) {
            return $visit->serviceJob;
        }

        $plan      = $visit->plan;
        $agreement = $plan?->agreement;

        $data = array_merge([
            'company_id'           => $visit->company_id,
            'customer_id'          => $plan?->customer_id ?? $agreement?->customer_id,
            'premises_id'          => $plan?->premises_id ?? $agreement?->premises_id,
            'agreement_id'         => $plan?->agreement_id ?? $agreement?->id,
            'title'                => $plan?->name ?? 'Recurring Service Visit',
            'status'               => 'scheduled',
            'scheduled_date_start' => $visit->scheduled_date ?? $visit->scheduled_for?->toDateString(),
            'notes'                => $visit->notes,
        ], $jobAttributes);

        $job = ServiceJob::create($data);

        $visit->update([
            'service_job_id' => $job->id,
            'status'         => 'scheduled',
        ]);

        RecurringVisitMaterialized::dispatch($visit->fresh(), $job);

        return $job;
    }

    /**
     * Materialize all pending visits for a plan that are due within a date range.
     *
     * @return Collection<int, ServiceJob>
     */
    public function materializeDueVisits(
        ServicePlan $plan,
        ?Carbon $until = null,
    ): Collection {
        $until ??= now()->addDays(14);

        $visits = ServicePlanVisit::query()
            ->where('service_plan_id', $plan->id)
            ->whereNull('service_job_id')
            ->where('status', 'pending')
            ->whereDate('scheduled_date', '<=', $until->toDateString())
            ->get();

        $jobs = collect();
        foreach ($visits as $visit) {
            $jobs->push($this->materializeVisitToJob($visit));
        }

        return $jobs;
    }

    // ── E: Regenerate visits when agreement updated ──────────────────────────

    /**
     * Regenerate upcoming visits for all active plans on an agreement.
     *
     * Cancels pending/future unlinked visits and recreates from today.
     * Fires RecurringPlanUpdated for each affected plan.
     *
     * @return array{plan_id: int, cancelled: int, created: int}[]
     */
    public function regeneratePlansFromAgreementUpdate(ServiceAgreement $agreement): array
    {
        $plans   = $agreement->servicePlans()->where('status', 'active')->get();
        $results = [];

        foreach ($plans as $plan) {
            $cancelled = ServicePlanVisit::query()
                ->where('service_plan_id', $plan->id)
                ->whereNull('service_job_id')
                ->where('status', 'pending')
                ->whereDate('scheduled_date', '>=', now()->toDateString())
                ->delete();

            $visits = $this->generateVisitsForPlan($plan);

            RecurringPlanUpdated::dispatch($plan);

            $results[] = [
                'plan_id'   => $plan->id,
                'cancelled' => $cancelled,
                'created'   => $visits->count(),
            ];
        }

        return $results;
    }

    // ── F: Full recurring pipeline ───────────────────────────────────────────

    /**
     * Run the full recurring sale pipeline for an accepted Quote.
     *
     * 1. Detect recurring service lines on the quote
     * 2. Create a ServicePlan per recurring type
     * 3. Generate initial visit schedule for each plan
     *
     * @return array{
     *     plans: Collection<int, ServicePlan>,
     *     visits: Collection<int, ServicePlanVisit>
     * }
     */
    public function runRecurringPipeline(
        Quote $quote,
        ServiceAgreement $agreement,
        ?Carbon $visitsUntil = null,
    ): array {
        $plans  = $this->createPlansFromRecurringLines($quote, $agreement);
        $visits = collect();

        foreach ($plans as $plan) {
            $generatedVisits = $this->generateVisitsForPlan($plan, until: $visitsUntil);
            $visits = $visits->merge($generatedVisits);
        }

        return compact('plans', 'visits');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Map a service_tracking_type string to a canonical recurrence_type.
     */
    protected function mapTrackingTypeToRecurrenceType(string $trackingType): string
    {
        $type = strtolower($trackingType);

        if (str_contains($type, 'maintenance') || str_contains($type, 'preventative')) {
            return self::RECURRENCE_MAINTENANCE;
        }

        if (str_contains($type, 'inspection') || str_contains($type, 'compliance')) {
            return str_contains($type, 'compliance')
                ? self::RECURRENCE_COMPLIANCE
                : self::RECURRENCE_INSPECTION;
        }

        return self::RECURRENCE_CONTRACT;
    }

    /**
     * Map a recurrence_type to a visit_type string for ServicePlanVisit.
     */
    protected function visitTypeFromRecurrenceType(?string $recurrenceType): string
    {
        return match ($recurrenceType) {
            self::RECURRENCE_MAINTENANCE => 'maintenance',
            self::RECURRENCE_INSPECTION  => 'inspection',
            self::RECURRENCE_COMPLIANCE  => 'compliance',
            default                      => 'service',
        };
    }

    /**
     * Advance a Carbon date by the plan's frequency and interval.
     */
    protected function advanceDate(Carbon $date, ?string $frequency, int $interval = 1): Carbon
    {
        return match ($frequency) {
            'daily'       => $date->addDays($interval),
            'weekly'      => $date->addWeeks($interval),
            'fortnightly' => $date->addWeeks(2 * $interval),
            'quarterly'   => $date->addMonthsNoOverflow(3 * $interval),
            'annual'      => $date->addYears($interval),
            default       => $date->addMonthsNoOverflow($interval),
        };
    }
}
