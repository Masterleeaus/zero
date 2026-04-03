<?php

namespace Modules\Timesheet\Services;

use Carbon\Carbon;

class WeekService
{
    public function weekStart(Carbon $date): Carbon
    {
        $startsOn = (int) config('timesheet.approvals.week_starts_on', 1); // 1=Mon
        $d = $date->copy()->startOfDay();

        // Carbon ISO: startOfWeek uses locale, we calculate manually.
        // Convert to ISO day of week 1..7
        $isoDow = (int) $d->isoWeekday();
        $diff = ($isoDow - $startsOn);
        if ($diff < 0) {
            $diff += 7;
        }
        return $d->subDays($diff)->startOfDay();
    }

    public function weekEnd(Carbon $date): Carbon
    {
        return $this->weekStart($date)->copy()->addDays(6)->endOfDay();
    }
}
