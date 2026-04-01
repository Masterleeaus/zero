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
