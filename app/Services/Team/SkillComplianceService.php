<?php

declare(strict_types=1);

namespace App\Services\Team;

use App\Models\Team\Certification;
use App\Models\Team\TechnicianSkill;
use Illuminate\Support\Collection;

class SkillComplianceService
{
    /**
     * Return all technicians in a company who have expired skills or certifications.
     *
     * Each item describes one gap:
     *   - user_id, user_name, gap_type (expired_skill|expired_cert), detail
     */
    public function getComplianceGaps(int $companyId): Collection
    {
        $gaps = collect();

        // ── 1. Expired skills ────────────────────────────────────────────────
        $expiredSkills = TechnicianSkill::query()
            ->join('users', 'users.id', '=', 'technician_skills.user_id')
            ->join('skill_definitions', 'skill_definitions.id', '=', 'technician_skills.skill_definition_id')
            ->where('users.company_id', $companyId)
            ->whereNotNull('technician_skills.expires_at')
            ->where('technician_skills.expires_at', '<', now()->toDateString())
            ->select(
                'technician_skills.user_id',
                'users.name as user_name',
                'skill_definitions.name as skill_name',
                'technician_skills.expires_at',
            )
            ->get()
            ->map(static fn ($row) => [
                'user_id'   => $row->user_id,
                'user_name' => $row->user_name,
                'gap_type'  => 'expired_skill',
                'detail'    => $row->skill_name . ' (expired ' . $row->expires_at . ')',
            ]);

        $gaps = $gaps->merge($expiredSkills);

        // ── 2. Expired certifications ────────────────────────────────────────
        $expiredCerts = Certification::withoutGlobalScope('company')
            ->join('users', 'users.id', '=', 'certifications.user_id')
            ->where('certifications.company_id', $companyId)
            ->where(static function ($q) {
                $q->where('certifications.status', 'expired')
                    ->orWhere(static function ($q2) {
                        $q2->whereNotNull('certifications.expires_at')
                            ->where('certifications.expires_at', '<', now()->toDateString());
                    });
            })
            ->select(
                'certifications.user_id',
                'users.name as user_name',
                'certifications.certification_name',
                'certifications.expires_at',
            )
            ->get()
            ->map(static fn ($row) => [
                'user_id'   => $row->user_id,
                'user_name' => $row->user_name,
                'gap_type'  => 'expired_cert',
                'detail'    => $row->certification_name . ' (expired ' . $row->expires_at . ')',
            ]);

        $gaps = $gaps->merge($expiredCerts);

        return $gaps->values();
    }

    /**
     * Generate a structured compliance report for a company.
     *
     * @return array{company_id: int, generated_at: string, total_gaps: int, gaps_by_type: array, gaps: array}
     */
    public function generateComplianceReport(int $companyId): array
    {
        $gaps = $this->getComplianceGaps($companyId);

        $byType = $gaps->groupBy('gap_type')
            ->map(static fn (Collection $items) => $items->count())
            ->toArray();

        return [
            'company_id'   => $companyId,
            'generated_at' => now()->toIso8601String(),
            'total_gaps'   => $gaps->count(),
            'gaps_by_type' => $byType,
            'gaps'         => $gaps->toArray(),
        ];
    }
}
