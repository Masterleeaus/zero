<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\JournalEntry;
use App\Models\Money\Payroll;

class PayrollPostingService
{
    public function __construct(protected AccountingService $accounting) {}

    public function buildPostingPayload(Payroll $payroll): array
    {
        $lines = [];

        // Debit: Wages Expense (total gross)
        $lines[] = [
            'account_code' => 'WAGES_EXPENSE',
            'type'         => 'debit',
            'amount'       => (float) $payroll->total_gross,
            'description'  => "Wages expense — payroll #{$payroll->id}",
        ];

        // Credit: Payroll Payable / Bank (net pay)
        $lines[] = [
            'account_code' => 'PAYROLL_PAYABLE',
            'type'         => 'credit',
            'amount'       => (float) $payroll->total_net,
            'description'  => "Net pay payable — payroll #{$payroll->id}",
        ];

        // Credit: Tax Withholding Payable
        $taxAmount = (float) $payroll->total_tax;
        if ($taxAmount > 0) {
            $lines[] = [
                'account_code' => 'TAX_WITHHOLDING_PAYABLE',
                'type'         => 'credit',
                'amount'       => $taxAmount,
                'description'  => "Tax withholding — payroll #{$payroll->id}",
            ];
        }

        // Credit: Superannuation / Benefit Payable (deductions = gross - net - tax)
        $deductions = (float) $payroll->total_gross - (float) $payroll->total_net - $taxAmount;
        if ($deductions > 0) {
            $lines[] = [
                'account_code' => 'SUPER_PAYABLE',
                'type'         => 'credit',
                'amount'       => round($deductions, 2),
                'description'  => "Superannuation/deductions — payroll #{$payroll->id}",
            ];
        }

        return [
            'payroll_id'  => $payroll->id,
            'company_id'  => $payroll->company_id,
            'description' => "Payroll run #{$payroll->id}",
            'lines'       => $lines,
        ];
    }

    public function postPayrollRun(Payroll $payroll): JournalEntry
    {
        return $this->accounting->postPayrollApproved($payroll);
    }

    public function preparePayrollForPosting(Payroll $payroll): array
    {
        $errors = [];

        if (! $payroll->isApproved()) {
            $errors[] = 'Payroll must be approved before posting.';
        }

        if ((float) $payroll->total_gross <= 0) {
            $errors[] = 'Payroll total gross must be greater than zero.';
        }

        if ($payroll->lines()->count() === 0) {
            $errors[] = 'Payroll has no lines.';
        }

        return [
            'ready'  => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    public function getPostingStatus(Payroll $payroll): string
    {
        $entry = JournalEntry::query()
            ->where('source_type', 'payroll')
            ->where('source_id', $payroll->id)
            ->first();

        if (! $entry) {
            return 'pending';
        }

        return $entry->status === 'posted' ? 'posted' : 'failed';
    }
}
