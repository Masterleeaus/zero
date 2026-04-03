<?php

namespace Modules\Timesheet\Services;

use Modules\Timesheet\Entities\Timesheet;

class TimesheetRepository
{
    public function creatorQuery()
    {
        return Timesheet::query()->forCreator();
    }
}
