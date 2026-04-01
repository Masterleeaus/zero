<?php

namespace App\Titan\Signals\Providers;

use App\Titan\Signals\Signal;
use App\Titan\Signals\SignalKind;
use App\Titan\Signals\SignalProviderInterface;
use App\Titan\Signals\SignalSeverity;
use Illuminate\Support\Facades\DB;

final class WorkSignalsProvider implements SignalProviderInterface
{
    public function sourceEngine(): string
    {
        return 'work';
    }

    public function getSignals(int $companyId, ?int $teamId = null, ?int $userId = null): array
    {
        $query = DB::table('service_jobs')
            ->where('company_id', $companyId)
            ->whereIn('status', ['scheduled', 'in_progress']);

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $unassigned = (int) (clone $query)
            ->whereNull('assigned_user_id')
            ->count();

        $signals = [];

        if ($unassigned > 0) {
            $signals[] = Signal::make([
                'type' => 'job.unassigned',
                'kind' => SignalKind::UNASSIGNED_JOBS,
                'severity' => SignalSeverity::RED,
                'title' => 'Unassigned jobs need dispatch',
                'body' => "There are {$unassigned} jobs without an assigned crew.",
                'company_id' => $companyId,
                'team_id' => $teamId,
                'user_id' => $userId,
                'payload' => ['count' => $unassigned],
                'meta' => ['count' => $unassigned],
                'source' => 'service_jobs',
                'origin' => 'database',
                'source_engine' => $this->sourceEngine(),
            ]);
        }

        // Module 4 — flag jobs blocked by incomplete required activities
        $blockedByActivities = (int) DB::table('service_jobs as sj')
            ->where('sj.company_id', $companyId)
            ->whereIn('sj.status', ['scheduled', 'in_progress'])
            ->whereExists(function ($q) {
                $q->from('job_activities as ja')
                    ->whereColumn('ja.service_job_id', 'sj.id')
                    ->where('ja.required', true)
                    ->where('ja.state', 'todo');
            })
            ->when($teamId, fn ($q) => $q->where('sj.team_id', $teamId))
            ->count();

        if ($blockedByActivities > 0) {
            $signals[] = Signal::make([
                'type'         => 'job.activities_pending',
                'kind'         => 'activities_pending',
                'severity'     => SignalSeverity::YELLOW,
                'title'        => 'Jobs have pending required activities',
                'body'         => "{$blockedByActivities} job(s) have required activities that must be completed.",
                'company_id'   => $companyId,
                'team_id'      => $teamId,
                'user_id'      => $userId,
                'payload'      => ['count' => $blockedByActivities],
                'meta'         => ['count' => $blockedByActivities],
                'source'       => 'job_activities',
                'origin'       => 'database',
                'source_engine' => $this->sourceEngine(),
            ]);
        }

        if ($signals === []) {
            $signals[] = Signal::make([
                'type' => 'work.ok',
                'kind' => 'work_ok',
                'severity' => SignalSeverity::GREEN,
                'title' => 'Work looks stable',
                'body' => 'No urgent dispatch issues detected.',
                'company_id' => $companyId,
                'team_id' => $teamId,
                'user_id' => $userId,
                'payload' => [],
                'source' => 'service_jobs',
                'origin' => 'database',
                'source_engine' => $this->sourceEngine(),
            ]);
        }

        return $signals;
    }
}
