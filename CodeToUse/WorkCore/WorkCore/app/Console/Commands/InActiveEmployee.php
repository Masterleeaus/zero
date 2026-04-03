<?php

namespace App\Console\Commands;

use App\Models\EmployeeDetails;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Onboarding\Entities\OnboardingCompletedTask;

class InActiveEmployee extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inactive-cleaner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The cleaner is set to inactive if he exit the company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $todayDate = Carbon::today();
            
            EmployeeDetails::with('user')
                ->where(function ($query) use ($todayDate) {
                    $query->whereDate('last_date', '<=', $todayDate)
                        ->orWhereDate('notice_period_end_date', '<=', $todayDate);
                })
                ->whereHas('user', function ($query) {
                    $query->where('status', 'active');
                })
                ->chunk(50, function ($cleaners) use ($todayDate) {
                    foreach ($cleaners as $cleaner) {

                        if (module_enabled('Onboarding')) {
                            // Check if offboarding steps are pending
                            $offboardingPending = OnboardingCompletedTask::where('employee_id', $cleaner->user_id)
                            ->where('type', 'offboard')
                            ->where('status', 'pending')
                            ->exists();
    
                            if (!$offboardingPending) {
                                // All offboarding steps are completed, change status to inactive and disable login
                                $this->deactivateEmployee($cleaner);
                            }
                        } else {
                            // If Onboarding module is not enabled, change status to inactive without offboarding check
                            $this->deactivateEmployee($cleaner);
                        }
                    }
                });
    }

    /**
     * Deactivate the cleaner and destroy their sessions.
     *
     * @param EmployeeDetails $cleaner
     */
    protected function deactivateEmployee(EmployeeDetails $cleaner)
    {
        $cleaner->user->status = 'deactive';
        $cleaner->user->login = 'disable';
        $cleaner->user->inactive_date = now();

        if (empty($cleaner->last_date) && !empty($cleaner->notice_period_end_date)) {
            $cleaner->last_date = $cleaner->notice_period_end_date;
            $cleaner->save();
        }

        $cleaner->user->save();

    }

}
