<?php

namespace Modules\Timesheet\Contracts;

use App\Models\User;

interface HrmProvider
{
    public function hourlyRateFor(User $user): ?float;
    public function overtimeMultiplierFor(User $user): float;
}
