<?php

declare(strict_types=1);

namespace App\Observers\Money;

use App\Models\Money\Payroll;
use App\Services\TitanMoney\AccountingService;

/**
 * PayrollObserver — auto-posting hook for payroll approval.
 *
 * On approval: Dr Wages Expense / Cr Bank Account + Cr Tax Payable
 */
class PayrollObserver
{
    public function __construct(
        private readonly AccountingService $accounting,
    ) {}

    public function updated(Payroll $payroll): void
    {
        if ($payroll->wasChanged('status') && $payroll->status === Payroll::STATUS_APPROVED) {
            $this->accounting->postPayrollApproved($payroll);
        }
    }
}
