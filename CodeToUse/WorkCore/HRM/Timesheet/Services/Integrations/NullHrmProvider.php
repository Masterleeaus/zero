<?php

namespace Modules\Timesheet\Services\Integrations;

use App\Models\User;
use Modules\Timesheet\Contracts\HrmProvider;

class NullHrmProvider implements HrmProvider
{
    public function hourlyRateFor(User $user): ?float
    {
        return null;
    }

    public function overtimeMultiplierFor(User $user): float
    {
        return 1.0;
    }
}
