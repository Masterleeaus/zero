<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\AgreementServiceConsumed;
use App\Events\Work\JobCompletedBillable;
use App\Events\Work\JobMarkedBillable;
use App\Events\Work\ServiceInvoiceGenerated;
use App\Models\Money\Invoice;
use App\Models\Money\InvoiceItem;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * JobBillingService
 *
 * Connects the field-service execution lifecycle to the Money domain.
 *
 * Responsibilities:
 *  - Mark a job as billable and emit the appropriate signal
 *  - Generate an Invoice from a completed billable ServiceJob
 *  - Attach agreement consumption events for recurring billing
 *  - Prepare recurring billing hooks on ServiceAgreement jobs
 */
class JobBillingService
{
    /**
     * Mark a job as billable.
     *
     * @throws ValidationException
     */
    public function markBillable(ServiceJob $job, ?float $hourlyRate = null): ServiceJob
    {
        if ($job->is_billable) {
            throw ValidationException::withMessages([
                'is_billable' => __('This job is already marked as billable.'),
            ]);
        }

        $job->update([
            'is_billable'   => true,
            'billable_rate' => $hourlyRate ?? $job->billable_rate,
        ]);

        JobMarkedBillable::dispatch($job->fresh());

        return $job;
    }

    /**
     * Generate an Invoice from a completed billable ServiceJob.
     *
     * Creates a draft invoice with one line item representing the labour
     * performed. Additional items (parts, materials) can be appended
     * separately via the InvoiceItem model.
     *
     * @throws ValidationException
     */
    public function generateInvoice(ServiceJob $job): Invoice
    {
        if (! $job->is_billable) {
            throw ValidationException::withMessages([
                'is_billable' => __('Cannot invoice a non-billable job. Mark it billable first.'),
            ]);
        }

        if ($job->invoice_id) {
            throw ValidationException::withMessages([
                'invoice_id' => __('An invoice has already been generated for this job.'),
            ]);
        }

        return DB::transaction(function () use ($job) {
            $duration   = $job->duration;
            $rate       = (float) ($job->billable_rate ?? 0);
            $lineTotal  = round($duration * $rate, 2);

            $invoiceNumber = $this->nextInvoiceNumber($job->company_id);

            $invoice = Invoice::create([
                'company_id'     => $job->company_id,
                'created_by'     => $job->created_by,
                'customer_id'    => $job->customer_id,
                'quote_id'       => $job->quote_id,
                'invoice_number' => $invoiceNumber,
                'title'          => __('Invoice for :title', ['title' => $job->title]),
                'status'         => 'draft',
                'issue_date'     => now()->toDateString(),
                'due_date'       => now()->addDays(30)->toDateString(),
                'currency'       => 'USD',
                'subtotal'       => $lineTotal,
                'tax'            => 0,
                'total'          => $lineTotal,
                'paid_amount'    => 0,
                'balance'        => $lineTotal,
            ]);

            // Labour line item
            if ($duration > 0) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => __('Labour – :title (:hours hrs @ :rate/hr)', [
                        'title' => $job->title,
                        'hours' => number_format($duration, 2),
                        'rate'  => number_format($rate, 2),
                    ]),
                    'quantity'    => $duration,
                    'unit_price'  => $rate,
                    'tax_rate'    => 0,
                ]);
            }

            // Link the invoice back to the job
            $job->update([
                'invoice_id'  => $invoice->id,
                'invoiced_at' => now(),
            ]);

            $job->setRelation('invoice', $invoice);

            ServiceInvoiceGenerated::dispatch($job, $invoice);

            return $invoice;
        });
    }

    /**
     * Handle a job reaching a completed-billable state.
     *
     * Called by the JobCompletedBillable listener or directly after
     * job completion. Emits the signal and optionally auto-generates
     * an invoice when the job has a rate configured.
     */
    public function handleJobCompletedBillable(ServiceJob $job): void
    {
        JobCompletedBillable::dispatch($job);

        // Auto-invoice when rate is configured and no invoice exists yet
        if ($job->billable_rate && ! $job->invoice_id) {
            $this->generateInvoice($job);
        }
    }

    /**
     * Record agreement service consumption for recurring billing.
     *
     * Should be called when a job linked to a ServiceAgreement is
     * completed. Emits the signal so that the agreement scheduler and
     * any billing automation can react.
     */
    public function recordAgreementConsumption(ServiceAgreement $agreement, ServiceJob $job): void
    {
        AgreementServiceConsumed::dispatch($agreement, $job);
    }

    /**
     * Prepare recurring billing on a job created from a ServiceAgreement.
     *
     * Marks the job as billable using the agreement's configured rate (if any)
     * and links it back to the agreement for consumption tracking.
     */
    public function prepareRecurringJob(ServiceJob $job): void
    {
        if (! $job->agreement_id) {
            return;
        }

        $agreement = $job->agreement;

        if (! $agreement) {
            return;
        }

        // Inherit billability from the agreement's quote if present
        if ($agreement->quote_id && ! $job->is_billable) {
            $job->update(['is_billable' => true]);
        }
    }

    /**
     * Return a cost/revenue summary for all jobs within a company.
     *
     * Provides aggregates for unbilled / invoiced / cancelled work,
     * suitable for dashboards, reporting, and AI routing.
     *
     * @return array{company_id: int, total_jobs: int, billable_jobs: int, unbilled_completed: int, invoiced_jobs: int, estimated_unbilled_revenue: float, invoiced_revenue: float}
     */
    public function revenueSummary(int $companyId): array
    {
        $query = ServiceJob::query()->where('company_id', $companyId);

        $totalJobs   = (clone $query)->count();
        $billable    = (clone $query)->where('is_billable', true)->count();
        $unbilledCompleted = (clone $query)
            ->where('is_billable', true)
            ->whereNull('invoice_id')
            ->where('status', 'completed')
            ->count();
        $invoiced = (clone $query)->whereNotNull('invoice_id')->count();

        // Estimated revenue from unbilled completed jobs (rate × duration approximation)
        $unbilledJobs = (clone $query)
            ->where('is_billable', true)
            ->whereNull('invoice_id')
            ->where('status', 'completed')
            ->whereNotNull('billable_rate')
            ->get(['billable_rate', 'scheduled_duration', 'date_start', 'date_end']);

        $estimatedUnbilled = $unbilledJobs->sum(function (ServiceJob $job) {
            return round((float) ($job->billable_rate ?? 0) * $job->duration, 2);
        });

        $invoicedRevenue = Invoice::query()
            ->where('company_id', $companyId)
            ->whereHas('serviceJob')
            ->sum('total');

        return [
            'company_id'               => $companyId,
            'total_jobs'               => $totalJobs,
            'billable_jobs'            => $billable,
            'unbilled_completed'       => $unbilledCompleted,
            'invoiced_jobs'            => $invoiced,
            'estimated_unbilled_revenue' => round((float) $estimatedUnbilled, 2),
            'invoiced_revenue'         => round((float) $invoicedRevenue, 2),
        ];
    }

    /**
     * Return unbilled completed jobs as a report array.
     *
     * Lists all billable completed jobs that have not yet been invoiced,
     * ordered by completion date ascending (oldest first).
     *
     * @return array{company_id: int, count: int, jobs: array<int, array{id: int, title: string, customer_id: int|null, completed_at: string|null, estimated_revenue: float}>}
     */
    public function unbilledCompletedReport(int $companyId): array
    {
        $jobs = ServiceJob::query()
            ->where('company_id', $companyId)
            ->where('is_billable', true)
            ->whereNull('invoice_id')
            ->where('status', 'completed')
            ->orderBy('date_end')
            ->get();

        return [
            'company_id' => $companyId,
            'count'      => $jobs->count(),
            'jobs'       => $jobs->map(fn (ServiceJob $job) => [
                'id'               => $job->id,
                'title'            => $job->title,
                'customer_id'      => $job->customer_id,
                'completed_at'     => $job->date_end?->toDateTimeString(),
                'estimated_revenue' => round((float) ($job->billable_rate ?? 0) * $job->duration, 2),
            ])->all(),
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Generate the next sequential invoice number for a company.
     */
    private function nextInvoiceNumber(int $companyId): string
    {
        $last = Invoice::query()
            ->where('company_id', $companyId)
            ->whereNotNull('invoice_number')
            ->orderByDesc('id')
            ->value('invoice_number');

        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            return 'INV-' . str_pad((int) $m[1] + 1, 5, '0', STR_PAD_LEFT);
        }

        return 'INV-00001';
    }
}
