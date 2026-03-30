<?php

namespace Modules\Inspection\Console\Commands;

use App\Models\Company;
use Carbon\Carbon;
use App\Models\UniversalSearch;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use App\Scopes\ActiveScope;
use App\Traits\UniversalSearchTrait;
use Illuminate\Support\Facades\DB;
use Modules\Inspection\Entities\ScheduleItems;
use Modules\Inspection\Entities\RecurringSchedule;
use Modules\Inspection\Entities\Schedule;

class AutoCreateRecurringSchedules extends Command
{

    use UniversalSearchTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-schedule-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create recurring schedules ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $recurringSchedules = RecurringSchedule::with(['recurrings'])->where('status', 'active')->get();

        foreach ($recurringSchedules as $recurring) {

            if (is_null($recurring->next_schedule_date)) {
                continue;
            }

            $totalExistingCount = $recurring->recurrings->count();

            if ($recurring->unlimited_recurring == 1 || ($totalExistingCount < $recurring->billing_cycle)) {

                if ($recurring->next_schedule_date->timezone($recurring->company->timezone)->isToday()) {
                    $this->makeSchedule($recurring);
                    $this->saveNextScheduleDate($recurring);
                }
            }
        }
    }

    private function saveNextScheduleDate($recurring)
    {
        $days = match ($recurring->rotation) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'bi-weekly' => now()->addWeeks(2),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addQuarter(),
            'half-yearly' => now()->addMonths(6),
            'annually' => now()->addYear(),
            default => now()->addDay(),
        };

        $recurring->next_schedule_date = $days->format('Y-m-d');
        $recurring->save();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function makeSchedule($scheduleData)
    {

        $recurring = $scheduleData;

        $schedule = new Schedule();
        $schedule->schedule_recurring_id = $recurring->id;
        $schedule->company_id = $recurring->company_id;
        $schedule->issue_date = now()->format('Y-m-d');
        $schedule->subject = $recurring->subject;
        $schedule->floor_id = $recurring->floor_id;
        $schedule->tower_id = $recurring->tower_id;
        $schedule->lokasi = $recurring->lokasi;
        $schedule->shift = $recurring->shift;
        $schedule->awal = $recurring->awal;
        $schedule->akhir = $recurring->akhir;

        $schedule->save();

        foreach ($recurring->items as $item) {

            $scheduleItem = ScheduleItems::create(
                [
                    'schedule_id' => $schedule->id,
                    'item_name' => $item->item_name
                ]
            );
        }

        // Log search
        $this->logSearchEntry($schedule->id, $schedule->subject, 'schedules.show', 'schedule');
    }

}
