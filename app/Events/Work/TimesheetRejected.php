<?php

declare(strict_types=1);

namespace App\Events\Work;

use App\Models\User;
use App\Models\Work\WeeklyTimesheet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimesheetRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly WeeklyTimesheet $timesheet,
        public readonly User $reviewer,
    ) {}
}
