<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\ContractHealthDegraded;
use App\Models\Work\ServiceAgreement;
use Illuminate\Support\Collection;

class ContractHealthService
{
    private const DEGRADED_THRESHOLD = 60;

    /**
     * Compute a health score (0–100) for the given agreement.
     *
     * Scoring formula:
     *  - SLA breach rate    : 40 points (0 breaches = 40pts, degrades proportionally)
     *  - Entitlement usage  : 30 points (unused = 30pts, degrades as consumption rises)
     *  - Renewal proximity  : 30 points (far from expiry = 30pts, near = fewer pts)
     */
    public function computeHealthScore(ServiceAgreement $agreement): int
    {
        $slaScore         = $this->computeSlaScore($agreement);
        $entitlementScore = $this->computeEntitlementScore($agreement);
        $renewalScore     = $this->computeRenewalScore($agreement);

        return (int) round($slaScore + $entitlementScore + $renewalScore);
    }

    /**
     * Recompute and persist the health score, firing ContractHealthDegraded if needed.
     */
    public function refreshHealthScore(ServiceAgreement $agreement): int
    {
        $previousScore = $agreement->getHealthScore();
        $newScore      = $this->computeHealthScore($agreement);
        $flags         = $this->buildHealthFlags($agreement);

        $agreement->update([
            'health_score' => $newScore,
            'health_flags' => $flags,
        ]);

        if ($newScore < self::DEGRADED_THRESHOLD && $previousScore >= self::DEGRADED_THRESHOLD) {
            ContractHealthDegraded::dispatch($agreement, $previousScore, $newScore);
        }

        return $newScore;
    }

    /**
     * Return all agreements below the degraded threshold for a company.
     */
    public function getUnhealthyContracts(int $companyId): Collection
    {
        return ServiceAgreement::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('health_score', '<', self::DEGRADED_THRESHOLD)
            ->orderBy('health_score')
            ->get();
    }

    // ── private scoring helpers ───────────────────────────────────────────────

    private function computeSlaScore(ServiceAgreement $agreement): float
    {
        $totalJobs = $agreement->jobs()->count();

        if ($totalJobs === 0) {
            return 40.0;
        }

        $breachCount = $agreement->slaBreaches()->count();
        $breachRate  = $breachCount / $totalJobs;

        return max(0.0, 40.0 * (1.0 - min(1.0, $breachRate)));
    }

    private function computeEntitlementScore(ServiceAgreement $agreement): float
    {
        $entitlements = $agreement->entitlements()
            ->where('is_unlimited', false)
            ->whereNotNull('max_visits')
            ->get();

        if ($entitlements->isEmpty()) {
            return 30.0;
        }

        $avgConsumption = $entitlements->avg(function ($e) {
            if ($e->max_visits === 0) {
                return 1.0;
            }

            return min(1.0, $e->visits_used / $e->max_visits);
        });

        return max(0.0, 30.0 * (1.0 - (float) $avgConsumption));
    }

    private function computeRenewalScore(ServiceAgreement $agreement): float
    {
        if ($agreement->expired_at === null) {
            return 30.0;
        }

        $daysUntilExpiry = (int) now()->diffInDays($agreement->expired_at, false);

        if ($daysUntilExpiry <= 0) {
            return 0.0;
        }

        $noticeDays = $agreement->renewal_notice_days ?? 30;

        if ($daysUntilExpiry >= $noticeDays * 2) {
            return 30.0;
        }

        return max(0.0, 30.0 * ($daysUntilExpiry / ($noticeDays * 2)));
    }

    private function buildHealthFlags(ServiceAgreement $agreement): array
    {
        $flags = [];

        if ($this->computeSlaScore($agreement) < 20) {
            $flags[] = 'high_sla_breach_rate';
        }

        if ($this->computeEntitlementScore($agreement) < 10) {
            $flags[] = 'entitlements_near_exhausted';
        }

        if ($agreement->expired_at !== null) {
            $days = (int) now()->diffInDays($agreement->expired_at, false);
            if ($days <= 0) {
                $flags[] = 'expired';
            } elseif ($days <= ($agreement->renewal_notice_days ?? 30)) {
                $flags[] = 'renewal_due_soon';
            }
        }

        return $flags;
    }
}
