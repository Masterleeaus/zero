<?php

namespace App\Events\Money;

use App\Models\Work\TimesheetSubmission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimesheetApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TimesheetSubmission $submission) {}
}
