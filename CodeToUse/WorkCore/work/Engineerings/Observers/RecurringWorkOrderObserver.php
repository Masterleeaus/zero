<?php

namespace Modules\Engineerings\Observers;

use App\Helper\Files;
use App\Models\Notification;
use Modules\Engineerings\Entities\RecurringWorkOrder;


class RecurringWorkOrderObserver
{
    public function creating(RecurringWorkOrder $schedule)
    {
        if (company()) {
            $schedule->company_id = company()->id;
        }

        $days = match ($schedule->rotation) {
            'daily' => $schedule->issue_date->addDay(),
            'weekly' => $schedule->issue_date->addWeek(),
            'bi-weekly' => $schedule->issue_date->addWeeks(2),
            'monthly' => $schedule->issue_date->addMonth(),
            'quarterly' => $schedule->issue_date->addQuarter(),
            'half-yearly' => $schedule->issue_date->addMonths(6),
            'annually' => $schedule->issue_date->addYear(),
            default => $schedule->issue_date->addDay(),
        };

        $schedule->next_schedule_date = $days->format('Y-m-d');
    }

    public function deleting(RecurringWorkOrder $schedule)
    {
        $notifyData = ['App\Notifications\ScheduleRecurringStatus', 'App\Notifications\NewRecurringSchedule',];
        \App\Models\Notification::deleteNotification($notifyData, $schedule->id);
    }

}
