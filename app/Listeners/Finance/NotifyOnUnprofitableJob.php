<?php

declare(strict_types=1);

namespace App\Listeners\Finance;

use App\Events\Finance\UnprofitableJobDetected;
use Illuminate\Support\Facades\Log;

class NotifyOnUnprofitableJob
{
    public function handle(UnprofitableJobDetected $event): void
    {
        Log::warning('Unprofitable job detected', [
            'job_id'           => $event->summary->job_id,
            'company_id'       => $event->summary->company_id,
            'total_cost'       => $event->summary->total_cost,
            'total_revenue'    => $event->summary->total_revenue,
            'gross_margin'     => $event->summary->gross_margin,
            'gross_margin_pct' => $event->summary->gross_margin_pct,
        ]);
    }
}
