<?php

namespace App\Listeners\Money;

use App\Events\Money\PayrollInputFinalized;
use App\Services\TitanMoney\PayrollPostingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostPayrollInputFinalizedToLedger implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(protected PayrollPostingService $payrollPosting) {}

    public function handle(PayrollInputFinalized $event): void
    {
        $payroll = $event->payroll;
        if ($payroll->isApproved()) {
            $this->payrollPosting->postPayrollRun($payroll);
        }
    }
}
