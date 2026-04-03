<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\FieldServiceAgreementSaleActivated;
use App\Events\Work\FieldServiceAgreementSaleCreated;
use App\Events\Work\FieldServiceAgreementSaleExtended;
use App\Events\Work\FieldServiceSaleApproved;
use App\Events\Work\FieldServiceSaleConvertedToJob;
use App\Events\Work\FieldServiceSaleConvertedToPlan;
use App\Events\Work\FieldServiceSaleCreated;
use App\Events\Work\SaleServiceCoverageApplied;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\ServicePlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FieldServiceSaleService
 *
 * Implements the sale-to-service conversion pipeline for the TitanZero FSM domain.
 *
 * Mirrors Odoo behaviours from:
 *   - fieldservice_sale   : quote acceptance → job generation
 *   - fieldservice_sale_agreement : quote acceptance → agreement activation
 *
 * All transitions use canonical host entities (Quote, ServiceJob, ServiceAgreement,
 * ServicePlan) and emit typed events rather than ad-hoc signals.
 *
 * Responsibilities:
 *   A) Mark a quote as field-service eligible and fire FieldServiceSaleCreated
 *   B) On quote approval, dispatch FieldServiceSaleApproved
 *   C) Convert accepted quote lines → ServiceJobs (one-per-sale or one-per-line)
 *   D) Convert accepted quote → ServicePlan (recurring service sold via agreement)
 *   E) Activate or extend a ServiceAgreement from an accepted quote
 */
class FieldServiceSaleService
{
    // ── A: Mark quote as field-service eligible ───────────────────────────────

    /**
     * Mark a quote as creating field-service work (fires FieldServiceSaleCreated).
     *
     * Call this when the user adds FSM-tracked items to a draft quote,
     * or when you want to explicitly tag a quote as field-service.
     */
    public function markAsFieldServiceSale(Quote $quote): void
    {
        FieldServiceSaleCreated::dispatch($quote);
    }

    // ── B: Approve / accept a quote ───────────────────────────────────────────

    /**
     * Fire FieldServiceSaleApproved when a quote enters accepted/approved status.
     *
     * This is a lightweight signal — actual job/plan creation is handled by
     * convertQuoteToJobs() and activateAgreementFromQuote(), which can be
     * called here or driven by a listener.
     */
    public function approveQuote(Quote $quote): void
    {
        FieldServiceSaleApproved::dispatch($quote);
    }

    // ── C: Convert quote → ServiceJobs ───────────────────────────────────────

    /**
     * Generate ServiceJob records from an accepted Quote.
     *
     * Mirrors Odoo fieldservice_sale._field_service_generation():
     *
     * Tracking mode 'sale' → one job for the whole quote (shared across all
     *   sale-tracked lines that do not yet have a job).
     * Tracking mode 'line' → one job per eligible quote line.
     *
     * @return Collection<int, ServiceJob>
     */
    public function convertQuoteToJobs(Quote $quote): Collection
    {
        $created = collect();

        /** @var Collection<int, QuoteItem> $candidates */
        $candidates = $quote->toServiceExecutionCandidates();

        if ($candidates->isEmpty()) {
            return $created;
        }

        DB::transaction(function () use ($quote, $candidates, &$created) {
            $saleLevelLines = $candidates->where('field_service_tracking', QuoteItem::TRACKING_SALE);
            $lineLevelLines = $candidates->where('field_service_tracking', QuoteItem::TRACKING_LINE);

            // One job for the whole quote (sale-level tracking)
            if ($saleLevelLines->isNotEmpty()) {
                $existingByQuote = ServiceJob::query()
                    ->where('quote_id', $quote->id)
                    ->whereNull('sale_line_id')
                    ->first();

                if (! $existingByQuote) {
                    $job = $this->createJobFromQuote($quote, null, $saleLevelLines->first());
                    $created->push($job);
                }
            }

            // One job per line (line-level tracking)
            foreach ($lineLevelLines as $line) {
                $existing = ServiceJob::query()
                    ->where('sale_line_id', $line->id)
                    ->first();

                if (! $existing) {
                    $job = $this->createJobFromQuote($quote, $line);
                    $created->push($job);
                }
            }
        });

        if ($created->isNotEmpty()) {
            FieldServiceSaleConvertedToJob::dispatch($quote, $created);
        }

        return $created;
    }

    /**
     * Build and persist a ServiceJob from a Quote (and optionally a specific line).
     */
    protected function createJobFromQuote(
        Quote $quote,
        ?QuoteItem $line = null,
        ?QuoteItem $saleLine = null,
    ): ServiceJob {
        $titleLine = $line ?? $saleLine;

        return ServiceJob::create([
            'company_id'   => $quote->company_id,
            'created_by'   => $quote->created_by,
            'customer_id'  => $quote->customer_id,
            'quote_id'     => $quote->id,
            'sale_line_id' => $line?->id,
            'premises_id'  => $quote->premises_id,
            'site_id'      => $quote->site_id,
            'title'        => $titleLine?->description ?? $quote->title ?? 'Field Service Job',
            'status'       => 'scheduled',
            'scheduled_at' => $quote->valid_until ?? now()->addDays(7),
            'notes'        => $quote->notes,
        ]);
    }

    // ── D: Convert quote → ServicePlan ───────────────────────────────────────

    /**
     * Create a ServicePlan from an accepted Quote, linked to an agreement.
     *
     * Used when a recurring service contract is sold through a quote.
     *
     * @param  array<string, mixed>  $planAttributes  Optional overrides for the ServicePlan.
     */
    public function convertQuoteToServicePlan(
        Quote $quote,
        ServiceAgreement $agreement,
        array $planAttributes = [],
    ): ServicePlan {
        $plan = DB::transaction(function () use ($quote, $agreement, $planAttributes) {
            $defaults = [
                'company_id'   => $quote->company_id,
                'created_by'   => $quote->created_by,
                'customer_id'  => $quote->customer_id,
                'agreement_id' => $agreement->id,
                'premises_id'  => $quote->premises_id,
                'name'         => $quote->title ?? 'Service Plan',
                'status'       => 'active',
                'is_active'    => true,
                'starts_on'    => $quote->issue_date ?? now()->toDateString(),
            ];

            return ServicePlan::create(array_merge($defaults, $planAttributes));
        });

        FieldServiceSaleConvertedToPlan::dispatch($quote, $plan);

        return $plan;
    }

    // ── E: Agreement activation / extension from a quote ─────────────────────

    /**
     * Activate a ServiceAgreement from an accepted Quote.
     *
     * Mirrors Odoo fieldservice_sale_agreement:
     *   SaleOrder._prepare_fsm_values() propagates agreement_id.
     *
     * If the agreement is in draft/pending status, it is set to active.
     * If it is already active, the quote is recorded as an extension (renewal).
     */
    public function activateAgreementFromQuote(
        Quote $quote,
        ServiceAgreement $agreement,
    ): ServiceAgreement {
        return DB::transaction(function () use ($quote, $agreement) {
            $wasActive = $agreement->isActive();

            $agreement->fill([
                'originating_quote_id' => $agreement->originating_quote_id ?? $quote->id,
                'quote_id'             => $agreement->quote_id ?? $quote->id,
            ]);

            if (! $wasActive) {
                $agreement->status = 'active';
                $agreement->save();

                FieldServiceAgreementSaleCreated::dispatch($quote, $agreement);
                FieldServiceAgreementSaleActivated::dispatch($agreement, $quote);
                SaleServiceCoverageApplied::dispatch($agreement, $quote);
            } else {
                $agreement->save();

                FieldServiceAgreementSaleExtended::dispatch($agreement, $quote);
                SaleServiceCoverageApplied::dispatch($agreement, $quote);
            }

            return $agreement->fresh();
        });
    }

    /**
     * Create a new ServiceAgreement from an accepted Quote.
     *
     * Used when no existing agreement is present and the sale creates one.
     *
     * @param  array<string, mixed>  $agreementAttributes  Optional overrides.
     */
    public function createAgreementFromQuote(
        Quote $quote,
        array $agreementAttributes = [],
    ): ServiceAgreement {
        $agreement = DB::transaction(function () use ($quote, $agreementAttributes) {
            $defaults = [
                'company_id'           => $quote->company_id,
                'customer_id'          => $quote->customer_id,
                'premises_id'          => $quote->premises_id,
                'quote_id'             => $quote->id,
                'originating_quote_id' => $quote->id,
                'status'               => 'active',
            ];

            return ServiceAgreement::create(array_merge($defaults, $agreementAttributes));
        });

        FieldServiceAgreementSaleCreated::dispatch($quote, $agreement);
        FieldServiceAgreementSaleActivated::dispatch($agreement, $quote);
        SaleServiceCoverageApplied::dispatch($agreement, $quote);

        return $agreement;
    }

    // ── F: Full pipeline ──────────────────────────────────────────────────────

    /**
     * Run the full sale-to-service pipeline for an accepted Quote.
     *
     * 1. Emit FieldServiceSaleApproved
     * 2. Create ServiceJobs from eligible quote lines
     * 3. If an agreement is provided, activate/extend it and optionally
     *    create a ServicePlan
     *
     * @param  array<string, mixed>  $options  {
     *     agreement?: ServiceAgreement,
     *     create_plan?: bool,
     *     plan_attributes?: array<string, mixed>
     * }
     * @return array{
     *     jobs: Collection<int, ServiceJob>,
     *     agreement: ServiceAgreement|null,
     *     plan: ServicePlan|null
     * }
     */
    public function runPipeline(Quote $quote, array $options = []): array
    {
        $this->approveQuote($quote);

        $jobs = $this->convertQuoteToJobs($quote);

        $agreement = null;
        $plan      = null;

        if (isset($options['agreement']) && $options['agreement'] instanceof ServiceAgreement) {
            $agreement = $this->activateAgreementFromQuote($quote, $options['agreement']);
        }

        if ($agreement && ! empty($options['create_plan'])) {
            $plan = $this->convertQuoteToServicePlan(
                $quote,
                $agreement,
                $options['plan_attributes'] ?? [],
            );
        }

        return compact('jobs', 'agreement', 'plan');
    }
}
