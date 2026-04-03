<?php

namespace App\Services\Work;

use App\Models\Work\DispatchConstraint;
use App\Models\Work\ServiceJob;
use App\Models\Premises\Premises;
use App\Models\User;
use App\Services\Team\CapabilityRegistryService;
use Illuminate\Support\Collection;

class DispatchConstraintService
{
    public function __construct(
        private readonly ?CapabilityRegistryService $capabilityRegistry = null,
    ) {}

    public function loadConstraints(int $companyId): Collection
    {
        return DispatchConstraint::forCompany($companyId)->active()->get();
    }

    public function evaluateSkillMatch(User $tech, ServiceJob $job): float
    {
        if (! $job->job_type_id) {
            return 1.0;
        }

        // Use the CapabilityRegistry when available for precise skill matching.
        if ($this->capabilityRegistry !== null && $job->jobType !== null) {
            $result = $this->capabilityRegistry->matchJobRequirements($tech, $job->jobType);

            if (! empty($result['missing']) || ! empty($result['expired'])) {
                return 0.0;
            }

            if (! empty($result['matched'])) {
                return 1.0;
            }
        }

        // Legacy fallback.
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
}
