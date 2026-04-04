<?php

declare(strict_types=1);

namespace App\Listeners\Work;

use App\Events\Work\JobCompleted;
use App\Services\Work\ContractHealthService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Recompute contract health score whenever a job under that contract completes.
 */
class UpdateContractHealthOnJobCompletion implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function __construct(private readonly ContractHealthService $healthService) {}

    public function handle(JobCompleted $event): void
    {
        $job = $event->job;

        if (! $job->agreement_id) {
            return;
        }

        try {
            $agreement = $job->agreement;

            if ($agreement) {
                $this->healthService->refreshHealthScore($agreement);
            }
        } catch (\Throwable $th) {
            Log::error('UpdateContractHealthOnJobCompletion: ' . $th->getMessage(), [
                'job_id' => $job->id,
            ]);
        }
    }
}
