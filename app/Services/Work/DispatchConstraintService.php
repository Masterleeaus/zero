<?php

namespace App\Services\Work;

use App\Models\Route\TechnicianAvailability;
use App\Models\Work\DispatchConstraint;
use App\Models\Work\ServiceJob;
use App\Models\Premises\Premises;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DispatchConstraintService
{
    public function loadConstraints(int $companyId): Collection
    {
        return DispatchConstraint::forCompany($companyId)->active()->get();
    }

    public function evaluateSkillMatch(User $tech, ServiceJob $job): float
    {
        if (! $job->job_type_id) {
            return 1.0;
        }

        if (method_exists($tech, 'skills') && $tech->skills()->where('name', optional($job->jobType)->name)->exists()) {
            return 1.0;
        }

        return 0.7;
    }

    public function evaluateTerritoryMatch(User $tech, Premises $premises): float
    {
        if (! $premises->postcode) {
            return 0.5;
        }

        if (method_exists($tech, 'territory') && $tech->territory) {
            return $tech->territory->coversZip($premises->postcode) ? 1.0 : 0.3;
        }

        return 0.5;
    }

    public function evaluateSlaUrgency(ServiceJob $job): float
    {
        if (! is_null($job->sla_deadline)) {
            $hoursUntilDeadline = now()->diffInHours($job->sla_deadline, false);

            if ($hoursUntilDeadline <= 0) {
                return 1.0;
            }
            if ($hoursUntilDeadline <= 4) {
                return 0.9;
            }
            if ($hoursUntilDeadline <= 24) {
                return 0.7;
            }
            if ($hoursUntilDeadline <= 72) {
                return 0.5;
            }
        }

        return 0.3;
    }

    /**
     * Evaluate whether a technician is available on the job's scheduled date.
     *
     * Returns 1.0 for confirmed availability, 0.5 when no schedule data is
     * available (assume available), and 0.0 when the technician is explicitly
     * unavailable on the required date.
     */
    public function evaluateAvailability(User $tech, ServiceJob $job): float
    {
        $scheduledDate = $job->scheduled_at ?? $job->scheduled_date_start;
        if (! $scheduledDate) {
            return 0.5; // No date set — cannot evaluate
        }

        $date = Carbon::parse($scheduledDate);
        $dayBit = (int) pow(2, $date->dayOfWeek === 0 ? 6 : $date->dayOfWeek - 1); // Mon=1 → bit0

        $availability = TechnicianAvailability::where('user_id', $tech->id)
            ->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date->toDateString());
            })
            ->first();

        if (! $availability) {
            return 0.5; // No schedule defined — assume available
        }

        // Check bitmask for the scheduled day
        return ($availability->active_days_mask & $dayBit) ? 1.0 : 0.0;
    }
}

