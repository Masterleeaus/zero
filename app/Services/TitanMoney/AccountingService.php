<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Account;
use App\Models\Money\JournalEntry;
use App\Models\Money\JournalLine;
use App\Models\Money\LedgerTransaction;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Record a single-entry ledger transaction against an account, updating its balance.
     */
    public function recordTransaction(
        int $accountId,
        int $companyId,
        string $type,
        float $amount,
        ?string $category = null,
        ?string $reference = null,
        ?string $description = null,
        ?int $journalEntryId = null,
    ): LedgerTransaction {
        return DB::transaction(function () use ($accountId, $companyId, $type, $amount, $category, $reference, $description, $journalEntryId) {
            $account = Account::withoutGlobalScope('company')->findOrFail($accountId);

            if (in_array($type, [LedgerTransaction::TYPE_INCOME, LedgerTransaction::TYPE_TRANSFER_IN], true)) {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }

            return LedgerTransaction::create([
                'account_id'       => $accountId,
                'company_id'       => $companyId,
                'journal_entry_id' => $journalEntryId,
                'type'             => $type,
                'amount'           => $amount,
                'category'         => $category,
                'reference'        => $reference,
                'description'      => $description,
                'transaction_date' => now(),
            ]);
        });
    }

    /**
     * Transfer funds between two accounts (double-entry single ledger).
     */
    public function transferFunds(
        int $fromAccountId,
        int $toAccountId,
        int $companyId,
        float $amount,
        ?string $reference = null,
    ): void {
        DB::transaction(function () use ($fromAccountId, $toAccountId, $companyId, $amount, $reference) {
            $this->recordTransaction($fromAccountId, $companyId, LedgerTransaction::TYPE_TRANSFER_OUT, $amount, 'Internal Transfer', $reference, "Transfer to Account #{$toAccountId}");
            $this->recordTransaction($toAccountId, $companyId, LedgerTransaction::TYPE_TRANSFER_IN, $amount, 'Internal Transfer', $reference, "Transfer from Account #{$fromAccountId}");
        });
    }

    /**
     * Post a balanced double-entry journal entry.
     * Automatically updates account balances based on gl_type.
     *
     * @param  int    $companyId
     * @param  string $reference  Unique per company
     * @param  string $date       Y-m-d
     * @param  array  $lines      Each line: ['account_id', 'debit', 'credit', 'description'?]
     * @param  string|null $description
     * @param  int|null    $createdBy
     */
    public function postJournalEntry(
        int $companyId,
        string $reference,
        string $date,
        array $lines,
        ?string $description = null,
        ?int $createdBy = null,
    ): JournalEntry {
        $totalDebit  = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');

        if (round((float) $totalDebit, 2) !== round((float) $totalCredit, 2)) {
            throw new \InvalidArgumentException('Journal entry is not balanced: debits do not equal credits.');
        }

        if ($totalDebit == 0) {
            throw new \InvalidArgumentException('Journal entry must have a non-zero value.');
        }

        return DB::transaction(function () use ($companyId, $reference, $date, $lines, $description, $createdBy) {
            $entry = JournalEntry::create([
                'company_id'  => $companyId,
                'created_by'  => $createdBy,
                'reference'   => $reference,
                'date'        => $date,
                'description' => $description,
            ]);

            foreach ($lines as $line) {
                $debit  = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                if ($debit <= 0 && $credit <= 0) {
                    continue;
                }

                $entry->lines()->create([
                    'account_id'  => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit'       => $debit,
                    'credit'      => $credit,
                ]);

                // Update account balance: debit-normal accounts (asset, expense) increase on debit
                $account = Account::withoutGlobalScope('company')->find($line['account_id']);
                if ($account) {
                    if ($account->isDebitNormal()) {
                        $account->balance += $debit;
                        $account->balance -= $credit;
                    } else {
                        $account->balance += $credit;
                        $account->balance -= $debit;
                    }
                    $account->save();
                }
            }

            return $entry;
        });
    }

    /**
     * Get an income/expense summary for a company over a date range.
     */
    public function getSummary(int $companyId, string $startDate, string $endDate): array
    {
        $transactions = LedgerTransaction::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $income  = $transactions->where('type', LedgerTransaction::TYPE_INCOME)->sum('amount');
        $expense = $transactions->where('type', LedgerTransaction::TYPE_EXPENSE)->sum('amount');

        return [
            'total_income'       => (float) $income,
            'total_expense'      => (float) $expense,
            'net_profit'         => (float) ($income - $expense),
            'transaction_count'  => $transactions->count(),
        ];
    }
}
