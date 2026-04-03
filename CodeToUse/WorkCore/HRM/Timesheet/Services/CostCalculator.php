<?php

namespace Modules\Timesheet\Services;

use Modules\Timesheet\Entities\Timesheet;

class CostCalculator
{
    /**
     * Compute total cost for a timesheet entry.
     * Returns null if rate is unknown or costing disabled.
     */
    public function computeCostTotal(Timesheet $t): ?float
    {
        if (!config('timesheet.features.costing_enabled', true)) {
            return null;
        }

        if ($t->fsm_rate_per_hour === null) {
            return null;
        }

        $hoursDecimal = ((int) $t->hours) + (((int) $t->minutes) / 60.0);
        $multiplier = $t->fsm_overtime_multiplier !== null
            ? (float) $t->fsm_overtime_multiplier
            : (float) config('timesheet.integrations.default_overtime_multiplier', 1.0);

        // Treat type 'overtime' as multiplier > 1
        if ($t->type !== null && strtolower((string) $t->type) !== 'overtime') {
            $multiplier = 1.0;
        }

        return round(((float) $t->fsm_rate_per_hour) * $hoursDecimal * $multiplier, 2);
    }
}
