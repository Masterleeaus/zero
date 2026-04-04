<?php

declare(strict_types=1);

namespace App\Events\Finance;

use App\Models\Finance\JobCostRecord;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCostRecorded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly JobCostRecord $costRecord) {}
}
