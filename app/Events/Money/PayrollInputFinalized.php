<?php

namespace App\Events\Money;

use App\Models\Money\Payroll;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollInputFinalized
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Payroll $payroll) {}
}
