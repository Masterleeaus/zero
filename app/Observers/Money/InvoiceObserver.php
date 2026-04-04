<?php

declare(strict_types=1);

namespace App\Observers\Money;

use App\Models\Money\Invoice;

/**
 * InvoiceObserver — auto-posting hook preparation (Phase 7).
 *
 * Wired to the `issued` lifecycle state transition.
 * Full journal auto-posting will be activated in Phase 7 once
 * the default chart of accounts is seeded with standard account codes.
 *
 * Expected journal entry when Invoice status transitions to `issued`:
 *   Dr: Accounts Receivable (asset)
 *   Cr: Income / Revenue (revenue)
 */
class InvoiceObserver
{
    public function __construct(
        private readonly \App\Services\TitanMoney\AccountingService $accounting,
    ) {}

    /**
     * Handle the Invoice "updated" event.
     * When an invoice transitions to `issued`, post a journal entry.
     */
    public function updated(Invoice $invoice): void
    {
        if ($invoice->wasChanged('status') && $invoice->status === 'issued') {
            $this->accounting->postInvoiceIssued($invoice);
        }
    }
}
