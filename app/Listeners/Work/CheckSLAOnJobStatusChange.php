<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\JobStageChanged;
use App\Services\Work\ContractSLAService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Check SLA status when a job stage changes.
 *
 * Records a breach if the job is now past its SLA window.
 */
class CheckSLAOnJobStatusChange implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly ContractSLAService $slaService) {}

    public function handle(JobStageChanged $event): void
    {
        $job = $event->job;

        if (! $job->agreement_id) {
            return;
        }

        try {
            $status = $this->slaService->checkSLAStatus($job);

            if ($status['at_risk'] && $job->agreement) {
                $actualHours = $status['response_elapsed_hours'] ?? $status['resolution_elapsed_hours'] ?? 0.0;

                if (! $status['response_ok'] && $job->agreement->sla_response_hours !== null) {
                    $this->slaService->recordBreach(
                        $job->agreement,
                        $job,
                        'response',
                        (float) $actualHours
                    );
                }

                if (! $status['resolution_ok'] && $job->agreement->sla_resolution_hours !== null) {
                    $this->slaService->recordBreach(
                        $job->agreement,
                        $job,
                        'resolution',
                        (float) $actualHours
                    );
                }
            }
        } catch (\Throwable $th) {
            Log::error('CheckSLAOnJobStatusChange: ' . $th->getMessage(), ['job_id' => $job->id]);
        }
    }
}
