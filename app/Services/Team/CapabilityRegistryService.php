<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\Models\Team\AvailabilityOverride;
use App\Models\Team\AvailabilityWindow;
use App\Models\Team\Certification;
use App\Models\Team\SkillDefinition;
use App\Models\Team\TechnicianSkill;
use App\Models\User;
use App\Models\Work\JobType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CapabilityRegistryService
{
    /**
     * Return the full skill profile for a technician.
     *
     * @return array{skills: Collection, certifications: Collection, availability: Collection}
     */
    public function getSkillProfile(User $user): array
    {
        return [
            'skills'         => $user->technicianSkills()->with('skillDefinition')->get(),
            'certifications' => $user->certifications()->get(),
            'availability'   => $user->availabilityWindows()->active()->get(),
        ];
    }

    /**
     * Check whether a technician holds a skill at or above the given minimum level.
     * Expired skills do NOT satisfy the check.
     */
    public function hasSkill(User $user, int $skillDefinitionId, string $minLevel = 'competent'): bool
    {
        /** @var TechnicianSkill|null $skill */
        $skill = $user->technicianSkills()
            ->where('skill_definition_id', $skillDefinitionId)
            ->active()
            ->first();

        if ($skill === null) {
            return false;
        }

        return $skill->meetsLevel($minLevel);
    }

    /**
     * Check whether a technician has an active (non-expired, non-revoked) certification.
     */
    public function hasCertification(User $user, string $certName): bool
    {
        return $this->getCertificationStatus($user, $certName) === Certification::STATUS_ACTIVE;
    }

    /**
     * Return the status string for the most recent matching certification.
     * Returns 'none' when no record exists.
     */
    public function getCertificationStatus(User $user, string $certName): string
    {
        /** @var Certification|null $cert */
        $cert = $user->certifications()
            ->where('certification_name', $certName)
            ->orderByDesc('issued_at')
            ->first();

        if ($cert === null) {
            return 'none';
        }

        // Hard gate: if the expiry date has passed, treat as expired regardless of stored status.
        if ($cert->expires_at !== null && $cert->expires_at->isPast()) {
            return Certification::STATUS_EXPIRED;
        }

        return $cert->status;
    }

    /**
     * Collect certifications across a company that are expiring within $withinDays days.
     */
    public function getExpiringCertifications(int $companyId, int $withinDays = 30): Collection
    {
        return Certification::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->expiringSoon($withinDays)
            ->with('user')
            ->orderBy('expires_at')
            ->get();
    }

    /**
     * Check whether a technician is available at the given Carbon datetime.
     *
     * Date-specific overrides take precedence over recurring windows.
     */
    public function isAvailable(User $user, Carbon $datetime): bool
    {
        $dateStr = $datetime->toDateString();

        // 1. Check for an override on this exact date.
        /** @var AvailabilityOverride|null $override */
        $override = $user->availabilityOverrides()
            ->where('date', $dateStr)
            ->first();

        if ($override !== null) {
            return $override->available;
        }

        // 2. Fall back to the recurring weekly window.
        $dayOfWeek = (int) $datetime->dayOfWeek; // 0=Sunday … 6=Saturday
        $timeStr   = $datetime->format('H:i:s');

        return $user->availabilityWindows()
            ->active()
            ->forDay($dayOfWeek)
            ->where('start_time', '<=', $timeStr)
            ->where('end_time', '>=', $timeStr)
            ->exists();
    }

    /**
     * Match a technician against all skill requirements for a job type.
     *
     * @return array{matched: array, missing: array, expired: array}
     */
    public function matchJobRequirements(User $user, JobType $jobType): array
    {
        $requirements = $jobType->skillRequirements()->with('skillDefinition')->get();

        // Eager-load all relevant skills in a single query to avoid N+1.
        $skillIds = $requirements->pluck('skill_definition_id')->unique()->values();

        /** @var \Illuminate\Support\Collection<int, TechnicianSkill> $skillMap */
        $skillMap = $user->technicianSkills()
            ->whereIn('skill_definition_id', $skillIds)
            ->get()
            ->keyBy('skill_definition_id');

        $matched = [];
        $missing = [];
        $expired = [];

        foreach ($requirements as $req) {
            /** @var TechnicianSkill|null $skill */
            $skill = $skillMap->get($req->skill_definition_id);

            if ($skill === null) {
                if ($req->is_mandatory) {
                    $missing[] = $req->skillDefinition->name;
                }
                continue;
            }

            if ($skill->isExpired()) {
                $expired[] = $req->skillDefinition->name;
                continue;
            }

            if ($skill->meetsLevel($req->minimum_level)) {
                $matched[] = $req->skillDefinition->name;
            } else {
                if ($req->is_mandatory) {
                    $missing[] = $req->skillDefinition->name;
                }
            }
        }

        return compact('matched', 'missing', 'expired');
    }
}
