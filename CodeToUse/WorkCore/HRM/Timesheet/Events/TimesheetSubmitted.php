<?php

namespace Modules\Timesheet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Timesheet\Entities\TimesheetSubmission;

class TimesheetSubmitted
{
    use Dispatchable, SerializesModels;

    public TimesheetSubmission $submission;

    public function __construct(TimesheetSubmission $submission)
    {
        $this->submission = $submission;
    }
}
