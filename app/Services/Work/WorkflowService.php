<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\InvoiceIssued;
use App\Events\InvoicePaid;
use App\Events\QuoteAccepted;
use App\Events\Work\JobCancelled;
use App\Events\Work\JobCompleted;
use App\Events\Work\JobStarted;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\Work\ServiceJob;
use Illuminate\Validation\ValidationException;

/**
 * WorkflowService
 *
 * Centralises all status transitions for the core lifecycle:
 *   enquiry → quote → scheduled → active → completed → invoiced → paid → recurring
 *
 * Every public method:
 *  1. Validates that the requested transition is allowed from the current state.
 *  2. Persists the status change.
 *  3. Fires the appropriate domain event so that listeners can react.
 */
class WorkflowService
{
    // ── Quote transitions ──────────────────────────────────────────────────

    /**
     * Allowed Quote status transitions.
     *
     * @var array<string, string[]>
     */
    private array $quoteTransitions = [
        'draft'     => ['sent', 'approved', 'rejected', 'expired'],
        'sent'      => ['accepted', 'rejected', 'expired', 'approved'],
        'approved'  => ['accepted', 'converted'],
        'accepted'  => ['converted'],
        'rejected'  => ['draft'],
        'expired'   => ['draft'],
        'converted' => [],
    ];

    /**
     * Transition a Quote to a new status.
     *
     * @throws ValidationException
     */
    public function transitionQuote(Quote $quote, string $newStatus): Quote
    {
        $this->assertTransitionAllowed('Quote', $quote->status, $newStatus, $this->quoteTransitions);

        $quote->update(['status' => $newStatus]);

        if ($newStatus === 'accepted') {
            QuoteAccepted::dispatch($quote->fresh());
        }

        return $quote->fresh();
    }

    // ── ServiceJob transitions ─────────────────────────────────────────────

    /**
     * Allowed ServiceJob status transitions.
     *
     * @var array<string, string[]>
     */
    private array $jobTransitions = [
        'scheduled'   => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed'   => ['cancelled'],
        'cancelled'   => [],
    ];

    /**
     * Transition a ServiceJob to a new status.
     *
     * @throws ValidationException
     */
    public function transitionJob(ServiceJob $job, string $newStatus): ServiceJob
    {
        $this->assertTransitionAllowed('ServiceJob', $job->status, $newStatus, $this->jobTransitions);

        $updates = ['status' => $newStatus];

        if ($newStatus === 'in_progress' && $job->date_start === null) {
            $updates['date_start'] = now();
        }

        if ($newStatus === 'completed' && $job->date_end === null) {
            $updates['date_end'] = now();
        }

        $job->update($updates);

        $fresh = $job->fresh();

        match ($newStatus) {
            'in_progress' => JobStarted::dispatch($fresh),
            'completed'   => JobCompleted::dispatch($fresh),
            'cancelled'   => JobCancelled::dispatch($fresh),
            default       => null,
        };

        return $fresh;
    }

    // ── Invoice transitions ────────────────────────────────────────────────

    /**
     * Allowed Invoice status transitions.
     *
     * @var array<string, string[]>
     */
    private array $invoiceTransitions = [
        'draft'     => ['issued', 'cancelled'],
        'issued'    => ['paid', 'overdue', 'cancelled'],
        'overdue'   => ['paid', 'cancelled'],
        'paid'      => [],
        'cancelled' => [],
    ];

    /**
     * Transition an Invoice to a new status.
     *
     * @throws ValidationException
     */
    public function transitionInvoice(Invoice $invoice, string $newStatus): Invoice
    {
        $this->assertTransitionAllowed('Invoice', $invoice->status, $newStatus, $this->invoiceTransitions);

        $updates = ['status' => $newStatus];

        if ($newStatus === 'issued' && $invoice->issue_date === null) {
            $updates['issue_date'] = now()->toDateString();
        }

        $invoice->update($updates);

        $fresh = $invoice->fresh();

        match ($newStatus) {
            'issued' => InvoiceIssued::dispatch($fresh),
            'paid'   => InvoicePaid::dispatch($fresh),
            default  => null,
        };

        return $fresh;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Return allowed next statuses for a Quote.
     *
     * @return string[]
     */
    public function quoteAllowedTransitions(string $currentStatus): array
    {
        return $this->quoteTransitions[$currentStatus] ?? [];
    }

    /**
     * Return allowed next statuses for a ServiceJob.
     *
     * @return string[]
     */
    public function jobAllowedTransitions(string $currentStatus): array
    {
        return $this->jobTransitions[$currentStatus] ?? [];
    }

    /**
     * Return allowed next statuses for an Invoice.
     *
     * @return string[]
     */
    public function invoiceAllowedTransitions(string $currentStatus): array
    {
        return $this->invoiceTransitions[$currentStatus] ?? [];
    }

    /**
     * Assert that a transition is allowed, throwing a ValidationException if not.
     *
     * @param  array<string, string[]>  $map
     *
     * @throws ValidationException
     */
    private function assertTransitionAllowed(string $modelLabel, string $from, string $to, array $map): void
    {
        $allowed = $map[$from] ?? [];

        if (! in_array($to, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => __(
                    ':model cannot transition from ":from" to ":to". Allowed: :allowed.',
                    [
                        'model'   => $modelLabel,
                        'from'    => $from,
                        'to'      => $to,
                        'allowed' => implode(', ', $allowed) ?: 'none',
                    ]
                ),
            ]);
        }
    }
}
