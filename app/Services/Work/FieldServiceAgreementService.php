<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\FieldServiceAgreementActivated;
use App\Events\Work\FieldServiceAgreementCancelled;
use App\Events\Work\FieldServiceAgreementCreated;
use App\Events\Work\FieldServiceAgreementExpired;
use App\Events\Work\FieldServiceAgreementRenewed;
use App\Events\Work\FieldServiceAgreementUpdated;
use App\Models\Money\Quote;
use App\Models\Premises\Premises;
use App\Models\Work\FieldServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use App\Models\Work\ServicePlanVisit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FieldServiceAgreementService
 *
 * Implements the contract-driven service lifecycle for fieldservice_sale_agreement
 * and fieldservice_sale_recurring.
 *
 * Pipeline:
 *   Quote approved
 *     → createAgreementFromQuote()
 *       → attachAgreementToPremises()
 *         → generateVisitsFromAgreement()
 *           → generateJobsFromAgreement()
 *             → syncAgreementBillingSchedule()
 */
class FieldServiceAgreementService
{
    /**
     * Create a FieldServiceAgreement from an accepted Quote.
     *
     * Mirrors Odoo fieldservice_sale_agreement: sale order confirmation
     * promotes a quote into a service agreement.
     */
    public function createAgreementFromQuote(Quote $quote, array $overrides = []): FieldServiceAgreement
    {
        return DB::transaction(function () use ($quote, $overrides): FieldServiceAgreement {
            $data = array_merge([
                'company_id'           => $quote->company_id,
                'created_by'           => $quote->created_by ?? $quote->user_id ?? null,
                'customer_id'          => $quote->customer_id,
                'premises_id'          => $quote->premises_id ?? null,
                'quote_id'             => $quote->id,
                'title'                => $overrides['title'] ?? ($quote->title ?? "Agreement #{$quote->id}"),
                'reference'            => $overrides['reference'] ?? "QTE-{$quote->id}",
                'start_date'           => $overrides['start_date'] ?? now()->toDateString(),
                'end_date'             => $overrides['end_date'] ?? null,
                'billing_cycle'        => $overrides['billing_cycle'] ?? 'monthly',
                'service_frequency'    => $overrides['service_frequency'] ?? 'monthly',
                'status'               => 'active',
                'auto_generate_jobs'   => $overrides['auto_generate_jobs'] ?? false,
                'auto_generate_visits' => $overrides['auto_generate_visits'] ?? true,
                'terms_json'           => $overrides['terms_json'] ?? null,
                'notes'                => $overrides['notes'] ?? null,
            ], $overrides);

            $agreement = FieldServiceAgreement::query()->create($data);

            event(new FieldServiceAgreementCreated($agreement));

            return $agreement;
        });
    }

    /**
     * Attach a FieldServiceAgreement to a specific premises.
     *
     * Mirrors Odoo fieldservice_sale_agreement: agreement linked to site/location.
     */
    public function attachAgreementToPremises(FieldServiceAgreement $agreement, Premises $premises): FieldServiceAgreement
    {
        $agreement->premises_id = $premises->id;
        $agreement->save();

        event(new FieldServiceAgreementUpdated($agreement));

        return $agreement;
    }

    /**
     * Generate projected ServicePlanVisit records from an active agreement.
     *
     * Resolves (or creates) a backing ServicePlan to satisfy the NOT NULL
     * service_plan_id FK on service_plan_visits, then projects visits according
     * to service_frequency for the agreement window.
     */
    public function generateVisitsFromAgreement(FieldServiceAgreement $agreement, int $limit = 12): Collection
    {
        // Ensure a backing ServicePlan exists for this FSA so that the
        // service_plan_id FK (NOT NULL) on service_plan_visits is satisfied.
        $plan = ServicePlan::query()
            ->where('company_id', $agreement->company_id)
            ->where('field_service_agreement_id', $agreement->id)
            ->first();

        if ($plan === null) {
            $plan = ServicePlan::query()->create([
                'company_id'        => $agreement->company_id,
                'created_by'        => $agreement->created_by,
                'customer_id'       => $agreement->customer_id,
                'premises_id'       => $agreement->premises_id,
                'title'             => $agreement->title ?? "Plan for FSA #{$agreement->id}",
                'frequency'         => $agreement->service_frequency ?? 'monthly',
                'start_date'        => $agreement->start_date,
                'end_date'          => $agreement->end_date,
                'is_active'         => true,
                'status'            => 'active',
                'field_service_agreement_id' => $agreement->id,
            ]);
        }

        $visits   = new Collection();
        $start    = $agreement->start_date ?? now()->toDate();
        $end      = $agreement->end_date;
        $interval = $this->frequencyInterval($agreement->service_frequency ?? 'monthly');

        $current = Carbon::instance($start);
        $count   = 0;

        while ($count < $limit) {
            if ($end !== null && $current->gt(Carbon::instance($end))) {
                break;
            }

            $visit = ServicePlanVisit::query()->create([
                'company_id'                 => $agreement->company_id,
                'created_by'                 => $agreement->created_by,
                'service_plan_id'            => $plan->id,
                'field_service_agreement_id' => $agreement->id,
                'visit_type'                 => 'scheduled',
                'scheduled_for'              => $current->clone(),
                'scheduled_date'             => $current->toDateString(),
                'status'                     => 'pending',
                'coverage_source'            => 'agreement',
                'sale_originated'            => $agreement->quote_id !== null,
                'notes'                      => "Generated from agreement #{$agreement->id}",
            ]);

            $visits->push($visit);
            $current->add($interval);
            $count++;
        }

        return $visits;
    }

    /**
     * Generate ServiceJob records for all pending visits under an agreement.
     *
     * If auto_generate_jobs is enabled, materialises jobs from projected visits.
     */
    public function generateJobsFromAgreement(FieldServiceAgreement $agreement): Collection
    {
        $jobs = new Collection();

        $pendingVisits = $agreement->visits()
            ->where('status', 'pending')
            ->whereNull('service_job_id')
            ->get();

        foreach ($pendingVisits as $visit) {
            $job = ServiceJob::query()->create([
                'company_id'          => $agreement->company_id,
                'created_by'          => $agreement->created_by,
                'customer_id'         => $agreement->customer_id,
                'premises_id'         => $agreement->premises_id,
                'recurring_source_id' => $agreement->id,
                'contract_visit_id'   => $visit->id,
                'title'               => 'Service visit – ' . ($visit->scheduled_date?->format('d M Y') ?? __('Date TBD')),
                'status'              => 'scheduled',
                'scheduled_at'        => $visit->scheduled_for,
            ]);

            $visit->update(['service_job_id' => $job->id, 'status' => 'scheduled']);
            $jobs->push($job);
        }

        return $jobs;
    }

    /**
     * Sync the agreement's billing schedule.
     *
     * Updates billing_cycle and optionally triggers an invoice generation signal.
     */
    public function syncAgreementBillingSchedule(FieldServiceAgreement $agreement, string $billingCycle): FieldServiceAgreement
    {
        $agreement->billing_cycle = $billingCycle;
        $agreement->save();

        event(new FieldServiceAgreementUpdated($agreement));

        return $agreement;
    }

    /**
     * Terminate an active agreement.
     *
     * Sets status to 'cancelled', fires FieldServiceAgreementCancelled.
     */
    public function terminateAgreement(FieldServiceAgreement $agreement, string $reason = ''): FieldServiceAgreement
    {
        $agreement->status = 'cancelled';
        $agreement->save();

        event(new FieldServiceAgreementCancelled($agreement, $reason));

        return $agreement;
    }

    /**
     * Mark an agreement as expired.
     *
     * Fires FieldServiceAgreementExpired.
     */
    public function expireAgreement(FieldServiceAgreement $agreement): FieldServiceAgreement
    {
        $agreement->status = 'expired';
        $agreement->save();

        event(new FieldServiceAgreementExpired($agreement));

        return $agreement;
    }

    /**
     * Renew an agreement by creating a new one with a shifted date window.
     *
     * Mirrors Odoo fieldservice_sale_agreement: renewal creates successor agreement.
     */
    public function renewAgreement(FieldServiceAgreement $agreement, array $overrides = []): FieldServiceAgreement
    {
        return DB::transaction(function () use ($agreement, $overrides): FieldServiceAgreement {
            $newStart = $agreement->end_date
                ? Carbon::instance($agreement->end_date)->addDay()
                : now();

            $newEnd = $agreement->end_date && $agreement->start_date
                ? $newStart->clone()->addDays(
                    Carbon::instance($agreement->start_date)->diffInDays(Carbon::instance($agreement->end_date))
                )
                : null;

            $data = array_merge([
                'company_id'           => $agreement->company_id,
                'created_by'           => $agreement->created_by,
                'customer_id'          => $agreement->customer_id,
                'premises_id'          => $agreement->premises_id,
                'quote_id'             => $agreement->quote_id,
                'title'                => $agreement->title,
                'reference'            => $agreement->reference ? "RENEWAL-{$agreement->reference}" : null,
                'start_date'           => $newStart->toDateString(),
                'end_date'             => $newEnd?->toDateString(),
                'billing_cycle'        => $agreement->billing_cycle,
                'service_frequency'    => $agreement->service_frequency,
                'status'               => 'active',
                'auto_generate_jobs'   => $agreement->auto_generate_jobs,
                'auto_generate_visits' => $agreement->auto_generate_visits,
                'terms_json'           => $agreement->terms_json,
            ], $overrides);

            $renewal = FieldServiceAgreement::query()->create($data);

            $agreement->status = 'renewed';
            $agreement->save();

            event(new FieldServiceAgreementRenewed($agreement, $renewal));
            event(new FieldServiceAgreementCreated($renewal));

            return $renewal;
        });
    }

    /**
     * Activate a draft agreement.
     */
    public function activateAgreement(FieldServiceAgreement $agreement): FieldServiceAgreement
    {
        $agreement->status = 'active';
        $agreement->save();

        event(new FieldServiceAgreementActivated($agreement));

        return $agreement;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function frequencyInterval(string $frequency): \DateInterval
    {
        return match ($frequency) {
            'weekly'      => new \DateInterval('P7D'),
            'fortnightly' => new \DateInterval('P14D'),
            'quarterly'   => new \DateInterval('P3M'),
            'annually'    => new \DateInterval('P1Y'),
            default       => new \DateInterval('P1M'),
        };
    }
}
