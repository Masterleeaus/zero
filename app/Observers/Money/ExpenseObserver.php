<?php

declare(strict_types=1);

namespace App\Observers\Money;

use App\Models\Money\Expense;

/**
 * ExpenseObserver — auto-posting hook preparation (Phase 7).
 *
 * Wired to the `approved` lifecycle transition on Expense.
 * Full journal auto-posting will be activated in Phase 7 once
 * the default chart of accounts is seeded with standard account codes.
 *
 * Expected journal entry when an Expense is approved:
 *   Dr: Expense Account (expense)
 *   Cr: Bank Account or Accounts Payable (asset / liability)
 */
class ExpenseObserver
{
    /**
     * Handle the Expense "updated" event.
     * When an expense transitions to `approved`, a journal entry should be posted.
     *
     * @todo Phase 7 — wire AccountingService::postJournalEntry() here.
     */
    public function updated(Expense $expense): void
    {
        if ($expense->wasChanged('status') && $expense->status === 'approved') {
            // Phase 7: post debit Expense Account + credit Bank/Payable via AccountingService
        }
    }
}
