<?php

namespace Modules\Timesheet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Timesheet\Entities\TimesheetTimer;

class TimerStarted
{
    use Dispatchable, SerializesModels;

    public TimesheetTimer $timer;

    public function __construct(TimesheetTimer $timer)
    {
        $this->timer = $timer;
    }
}
