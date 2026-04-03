<?php

namespace Modules\Timesheet\Services\Integrations;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Timesheet\Contracts\HrmProvider;

class CoreHrmProvider implements HrmProvider
{
    public function hourlyRateFor(User $user): ?float
    {
        $table = config('timesheet.integrations.core_hrm_table', 'employees');
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        $userCol = config('timesheet.integrations.core_hrm_user_column', 'user_id');
        $rateCol = config('timesheet.integrations.core_hrm_rate_column', 'rate_per_hour');

        $rate = DB::table($table)->where($userCol, $user->id)->value($rateCol);
        return $rate !== null ? (float) $rate : null;
    }

    public function overtimeMultiplierFor(User $user): float
    {
        $mult = config('timesheet.integrations.default_overtime_multiplier', 1.0);
        return max(1.0, (float) $mult);
    }
}
