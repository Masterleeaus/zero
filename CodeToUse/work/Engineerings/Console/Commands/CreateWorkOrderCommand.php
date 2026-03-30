<?php

namespace Modules\Engineerings\Console\Commands;

use App\Models\Company;
use Carbon\Carbon;
use App\Models\UniversalSearch;
use App\Scopes\CompanyScope;
use Illuminate\Console\Command;
use App\Scopes\ActiveScope;
use App\Traits\UniversalSearchTrait;
use Illuminate\Support\Facades\DB;
use Modules\Engineerings\Entities\RecurringWorkOrder;
use Modules\Engineerings\Entities\WorkOrder;

class CreateWorkOrderCommand extends Command
{

    use UniversalSearchTrait;

      /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-workorder-create';

      /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create recurring work order';

      /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $recurringWorkOrder = RecurringWorkOrder::with(['recurrings'])->where('company_id', $company->id)->where('status', 'active')->get();

            foreach ($recurringWorkOrder as $recurring) {

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
    }

    private function saveNextScheduleDate($recurring)
    {
        $days = match ($recurring->rotation) {
            'daily'       => now()->addDay(),
            'weekly'      => now()->addWeek(),
            'bi-weekly'   => now()->addWeeks(2),
            'monthly'     => now()->addMonth(),
            'quarterly'   => now()->addQuarter(),
            'half-yearly' => now()->addMonths(6),
            'annually'    => now()->addYear(),
            default       => now()->addDay(),
        };

        $recurring->next_schedule_date = $days->format('Y-m-d');
        $recurring->save();
    }

      /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function makeSchedule($recurringsData)
    {
        $number = WorkOrder::lastInvoiceNumber() + 1;
        $zero   = '';
        if (strlen($number) < 4) {
            for ($i = 0; $i < 4 - strlen($number); $i++) {
                $zero = '0' . $zero;
            }
        }
        $nomor = "WO-" . Carbon::now()->format('ym') . "-" . $zero . $number;

        $recurring                         = $recurringsData;
        $workOrder                         = new WorkOrder();
        $workOrder->workorder_recurring_id = $recurring->id;
        $workOrder->workrequest_id         = $recurring->workrequest_id;
        $workOrder->category               = $recurring->category;
        $workOrder->priority               = $recurring->priority;
        $workOrder->status                 = $recurring->status_wo;
        $workOrder->work_description       = $recurring->work_description;
        $workOrder->schedule_start         = $recurring->schedule_start;
        $workOrder->schedule_finish        = $recurring->schedule_finish;
        $workOrder->estimate_hours         = $recurring->estimate_hours;
        $workOrder->estimate_minutes       = $recurring->estimate_minutes;
        $workOrder->actual_start           = $recurring->actual_start;
        $workOrder->actual_finish          = $recurring->actual_finish;
        $workOrder->actual_hours           = $recurring->actual_hours;
        $workOrder->actual_minutes         = $recurring->actual_minutes;
        $workOrder->user_id                = $recurring->user_id;
        $workOrder->assets_id              = $recurring->assets_id;
        $workOrder->nomor_wo               = $nomor;
        $workOrder->created_by             = user()->id;
        $workOrder->save();

          // Log search
        $this->logSearchEntry($workOrder->id, $workOrder->work_description, 'work.show', 'work');
    }

}
