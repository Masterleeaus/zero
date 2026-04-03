<?php

namespace Modules\Timesheet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Timesheet\Entities\TimesheetSubmission;

class TimesheetReviewed
{
    use Dispatchable, SerializesModels;

    public TimesheetSubmission $submission;
    public string $decision; // approved|rejected

    public function __construct(TimesheetSubmission $submission, string $decision)
    {
        $this->submission = $submission;
        $this->decision = $decision;
    }
}
