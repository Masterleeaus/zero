<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Record a financial transaction.
     */
    public function recordTransaction($accountId, $storeId, $type, $amount, $category = null, $reference = null, $description = null)
    {
        return DB::transaction(function () use ($accountId, $storeId, $type, $amount, $category, $reference, $description) {
            $account = Account::findOrFail($accountId);

            if ($type === 'income' || $type === 'transfer_in') {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }

            return Transaction::create([
                'account_id' => $accountId,
                'store_id' => $storeId,
                'type' => $type,
                'amount' => $amount,
                'category' => $category,
                'reference' => $reference,
                'description' => $description,
                'transaction_date' => now(),
            ]);
        });
    }

    /**
     * Transfer funds between accounts.
     */
    public function transferFunds($fromAccountId, $toAccountId, $storeId, $amount, $reference = null)
    {
        return DB::transaction(function () use ($fromAccountId, $toAccountId, $storeId, $amount, $reference) {
            $this->recordTransaction($fromAccountId, $storeId, 'transfer_out', $amount, 'Internal Transfer', $reference, "Transfer to Account #{$toAccountId}");
            $this->recordTransaction($toAccountId, $storeId, 'transfer_in', $amount, 'Internal Transfer', $reference, "Transfer from Account #{$fromAccountId}");
        });
    }

    /**
     * Get summary for reports.
     */
    public function getSummary($storeId, $startDate, $endDate)
    {
        $transactions = Transaction::where('store_id', $storeId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_profit' => $income - $expense,
            'transaction_count' => $transactions->count(),
        ];
    }
}
