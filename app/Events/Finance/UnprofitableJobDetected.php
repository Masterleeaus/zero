<?php

declare(strict_types=1);

namespace App\Events\Finance;

use App\Models\Finance\JobFinancialSummary;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnprofitableJobDetected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly JobFinancialSummary $summary) {}
}
