<?php

declare(strict_types=1);

namespace App\Services\Predict;

use App\Models\Facility\SiteAsset;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;

class PredictionSignalExtractorService
{
    /**
     * Extract signals for asset failure prediction.
     *
     * Returns an array of signal descriptors:
     * [[ 'type' => string, 'value' => mixed, 'weight' => float ], ...]
     */
    public function extractAssetSignals(SiteAsset $asset): array
    {
        $signals = [];

        // Age signal
        if ($asset->install_date) {
            $ageYears = $asset->install_date->diffInYears(now());
            $signals[] = [
                'type'   => 'asset_age_years',
                'value'  => $ageYears,
                'weight' => $this->ageWeight($ageYears),
            ];
        }

        // Condition signal
        $conditionWeight = match ($asset->condition_status ?? 'good') {
            'critical' => 0.90,
            'poor'     => 0.70,
            'fair'     => 0.45,
            'good'     => 0.20,
            'new'      => 0.05,
            default    => 0.30,
        };
        $signals[] = [
            'type'   => 'condition_status',
            'value'  => $asset->condition_status ?? 'unknown',
            'weight' => $conditionWeight,
        ];

        // Days since last service
        if ($asset->last_serviced_at) {
            $daysSince = $asset->last_serviced_at->diffInDays(now());
            $interval  = $asset->maintenance_interval_days ?? 90;
            $overduePct = $interval > 0 ? round($daysSince / $interval, 4) : 1.0;
            $signals[] = [
                'type'   => 'last_service_days_ago',
                'value'  => $daysSince,
                'weight' => min((float) $overduePct, 1.0),
            ];
        }

        // Maintenance overdue flag
        if ($asset->isMaintenanceDue()) {
            $signals[] = [
                'type'   => 'maintenance_overdue',
                'value'  => true,
                'weight' => 0.75,
            ];
        }

        // Inspection overdue flag
        if ($asset->isInspectionDue()) {
            $signals[] = [
                'type'   => 'inspection_overdue',
                'value'  => true,
                'weight' => 0.65,
            ];
        }

        // Service event failure history (repairs)
        $recentRepairs = $asset->serviceEvents()
            ->where('event_type', 'repair')
            ->where('event_date', '>=', now()->subYear())
            ->count();

        $signals[] = [
            'type'   => 'repairs_last_12_months',
            'value'  => $recentRepairs,
            'weight' => min($recentRepairs * 0.15, 0.90),
        ];

        // Warranty status
        $underWarranty = $asset->isUnderWarranty();
        $signals[] = [
            'type'   => 'under_warranty',
            'value'  => $underWarranty,
            'weight' => $underWarranty ? 0.05 : 0.25,
        ];

        return $signals;
    }

    /**
     * Extract job history signals for demand forecasting.
     */
    public function extractJobHistorySignals(int $companyId, string $serviceType): array
    {
        $signals = [];

        $jobsLast90 = ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays(90))
            ->when($serviceType !== '', static function ($q) use ($serviceType) {
                $q->where('type', $serviceType);
            })
            ->count();

        $jobsLast30 = ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->subDays(30))
            ->when($serviceType !== '', static function ($q) use ($serviceType) {
                $q->where('type', $serviceType);
            })
            ->count();

        $avgPer30Days = $jobsLast90 > 0 ? round($jobsLast90 / 3, 2) : 0;
        $trend        = $avgPer30Days > 0 ? round($jobsLast30 / $avgPer30Days, 4) : 1.0;

        $signals[] = [
            'type'   => 'job_volume_last_30_days',
            'value'  => $jobsLast30,
            'weight' => min($jobsLast30 / 100, 1.0),
        ];

        $signals[] = [
            'type'   => 'demand_trend_ratio',
            'value'  => $trend,
            'weight' => min(abs($trend - 1.0), 1.0),
        ];

        return $signals;
    }

    /**
     * Extract SLA risk signals for a service agreement.
     */
    public function extractSLASignals(ServiceAgreement $agreement): array
    {
        $signals = [];

        // Pending jobs
        $pendingJobs = $agreement->jobs()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $signals[] = [
            'type'   => 'open_job_count',
            'value'  => $pendingJobs,
            'weight' => min($pendingJobs * 0.10, 0.80),
        ];

        // Avg job duration vs SLA window
        $avgDurationHours = $agreement->jobs()
            ->where('status', 'completed')
            ->whereNotNull('date_start')
            ->whereNotNull('date_end')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, date_start, date_end)) as avg_hours')
            ->value('avg_hours');

        if ($avgDurationHours !== null) {
            $signals[] = [
                'type'   => 'avg_job_duration_hours',
                'value'  => round((float) $avgDurationHours, 2),
                'weight' => min((float) $avgDurationHours / 48.0, 1.0),
            ];
        }

        // Agreement status
        $signals[] = [
            'type'   => 'agreement_status',
            'value'  => $agreement->status,
            'weight' => $agreement->status === 'active' ? 0.10 : 0.60,
        ];

        // Overdue visits
        $overdueVisits = $agreement->visits()
            ->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_date', '<', now()->toDateString())
            ->count();

        $signals[] = [
            'type'   => 'overdue_visits',
            'value'  => $overdueVisits,
            'weight' => min($overdueVisits * 0.20, 0.90),
        ];

        return $signals;
    }

    /**
     * Extract capacity gap signals for a given date.
     */
    public function extractCapacitySignals(int $companyId, Carbon $date): array
    {
        $signals = [];

        $dayOfWeek = $date->dayOfWeek;

        // Jobs scheduled for that day
        $scheduledJobs = ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->whereDate('date_start', $date->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $signals[] = [
            'type'   => 'scheduled_job_count',
            'value'  => $scheduledJobs,
            'weight' => min($scheduledJobs / 20, 1.0),
        ];

        // Active technician count (approximation via recent jobs)
        $activeTechs = ServiceJob::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('date_start', '>=', now()->subDays(14))
            ->whereNotNull('assigned_user_id')
            ->distinct('assigned_user_id')
            ->count('assigned_user_id');

        $signals[] = [
            'type'   => 'active_technician_count',
            'value'  => $activeTechs,
            'weight' => $activeTechs > 0 ? min($scheduledJobs / ($activeTechs * 8), 1.0) : 1.0,
        ];

        $signals[] = [
            'type'   => 'forecast_day_of_week',
            'value'  => $dayOfWeek,
            'weight' => in_array($dayOfWeek, [1, 2, 3, 4, 5], true) ? 0.20 : 0.50,
        ];

        return $signals;
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function ageWeight(int $ageYears): float
    {
        return match (true) {
            $ageYears >= 15 => 0.90,
            $ageYears >= 10 => 0.70,
            $ageYears >= 7  => 0.50,
            $ageYears >= 5  => 0.35,
            $ageYears >= 3  => 0.20,
            default         => 0.10,
        };
    }
}
