<?php

namespace App\Services\Work;

use App\Models\Work\ServiceAgreement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AgreementSchedulerService
{
    public function runDueAgreements(int $companyId): array
    {
        $due = $this->dueAgreementsQuery($companyId)->get();

        $results = [
            'company_id' => $companyId,
            'processed'  => 0,
            'created'    => 0,
            'skipped'    => 0,
            'agreements' => [],
        ];

        foreach ($due as $agreement) {
            $results['processed']++;

            if ($this->hasDuplicateJob($agreement)) {
                $results['skipped']++;
                $results['agreements'][] = [
                    'id'     => $agreement->id,
                    'reason' => 'duplicate',
                ];
                continue;
            }

            $job = $agreement->createJob();
            $agreement->scheduleNext();

            $results['created']++;
            $results['agreements'][] = [
                'id'      => $agreement->id,
                'job_id'  => $job->id,
                'next_at' => $agreement->next_run_at,
            ];
        }

        return $results;
    }

    public function previewUpcomingRuns(int $companyId, ?Carbon $until = null): array
    {
        $until ??= now()->addMonth();

        $agreements = $this->dueAgreementsQuery($companyId)
            ->orWhereBetween('next_run_at', [now(), $until])
            ->get();

        return [
            'company_id' => $companyId,
            'previewed'  => $agreements->count(),
            'agreements' => $agreements->map(fn ($a) => [
                'id'        => $a->id,
                'next_run'  => $a->next_run_at,
                'status'    => $a->status,
                'frequency' => $a->frequency,
            ])->all(),
        ];
    }

    protected function dueAgreementsQuery(int $companyId)
    {
        return ServiceAgreement::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->notPaused()
            ->whereNull('expired_at')
            ->whereNotNull('next_run_at')
            ->whereDate('next_run_at', '<=', now());
    }

    protected function hasDuplicateJob(ServiceAgreement $agreement): bool
    {
        if (! $agreement->next_run_at) {
            return false;
        }

        return $agreement->jobs()
            ->whereDate('scheduled_at', $agreement->next_run_at->toDateString())
            ->exists();
    }
}
