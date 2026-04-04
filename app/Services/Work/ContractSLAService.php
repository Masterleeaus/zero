<?php

declare(strict_types=1);

namespace App\Services\Work;

use App\Events\Work\ContractSLABreached;
use App\Models\Work\ContractSLABreach;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContractSLAService
{
    /**
     * Check the SLA status of the given job against its agreement.
     *
     * SLA clock starts when the job transitions to 'assigned'.
     *
     * @return array{
     *     job_id: int,
     *     agreement_id: int|null,
     *     sla_response_hours: int|null,
     *     sla_resolution_hours: int|null,
     *     response_ok: bool,
     *     resolution_ok: bool,
     *     response_elapsed_hours: float|null,
     *     resolution_elapsed_hours: float|null,
     *     at_risk: bool
     * }
     */
    public function checkSLAStatus(ServiceJob $job): array
    {
        $agreement = $job->agreement_id ? ServiceAgreement::find($job->agreement_id) : null;

        $base = [
            'job_id'                   => $job->id,
            'agreement_id'             => $agreement?->id,
            'sla_response_hours'       => $agreement?->sla_response_hours,
            'sla_resolution_hours'     => $agreement?->sla_resolution_hours,
            'response_ok'              => true,
            'resolution_ok'            => true,
            'response_elapsed_hours'   => null,
            'resolution_elapsed_hours' => null,
            'at_risk'                  => false,
        ];

        if ($agreement === null) {
            return $base;
        }

        $clockStart = $this->getSLAClockStart($job);

        if ($clockStart === null) {
            return $base;
        }

        $now = Carbon::now();
        $elapsedHours = $clockStart->diffInMinutes($now) / 60.0;

        if ($agreement->sla_response_hours !== null) {
            $base['response_elapsed_hours'] = round($elapsedHours, 2);
            $base['response_ok'] = $elapsedHours <= $agreement->sla_response_hours;
        }

        if ($agreement->sla_resolution_hours !== null) {
            $base['resolution_elapsed_hours'] = round($elapsedHours, 2);
            $base['resolution_ok'] = $elapsedHours <= $agreement->sla_resolution_hours;
        }

        $base['at_risk'] = ! $base['response_ok'] || ! $base['resolution_ok'];

        return $base;
    }

    /**
     * Record a confirmed SLA breach and fire the ContractSLABreached event.
     */
    public function recordBreach(
        ServiceAgreement $agreement,
        ServiceJob $job,
        string $breachType,
        float $actualHours
    ): ContractSLABreach {
        $slaHours = $breachType === 'response'
            ? (int) $agreement->sla_response_hours
            : (int) $agreement->sla_resolution_hours;

        $breach = ContractSLABreach::create([
            'company_id'   => $agreement->company_id,
            'agreement_id' => $agreement->id,
            'job_id'       => $job->id,
            'breach_type'  => $breachType,
            'sla_hours'    => $slaHours,
            'actual_hours' => $actualHours,
            'breached_at'  => now(),
        ]);

        ContractSLABreached::dispatch($agreement, $job, $breach);

        return $breach;
    }

    /**
     * Get all jobs currently at risk of an SLA breach for the given company.
     */
    public function getAtRiskJobs(int $companyId): Collection
    {
        return ServiceJob::where('company_id', $companyId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('agreement_id')
            ->with('agreement')
            ->get()
            ->filter(function (ServiceJob $job) {
                $status = $this->checkSLAStatus($job);

                return $status['at_risk'];
            })
            ->values();
    }

    /**
     * Determine the SLA clock start: when the job was assigned.
     */
    protected function getSLAClockStart(ServiceJob $job): ?Carbon
    {
        if (isset($job->assigned_at)) {
            return Carbon::parse($job->assigned_at);
        }

        if (isset($job->scheduled_at)) {
            return Carbon::parse($job->scheduled_at);
        }

        return Carbon::parse($job->created_at);
    }
}
