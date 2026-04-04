<?php

declare(strict_types=1);

namespace App\Listeners\Trust;

use App\Events\Work\JobCompleted;
use App\Models\Trust\TrustLedgerEntry;
use App\Services\Trust\TrustLedgerService;

class RecordJobCompletionOnLedger
{
    public function __construct(protected TrustLedgerService $ledger) {}

    public function handle(JobCompleted $event): void
    {
        $job = $event->job;

        $this->ledger->record(
            TrustLedgerEntry::ENTRY_TYPES[0], // 'job_completed'
            $job,
            [
                'job_id'     => $job->id,
                'outcome'    => $job->outcome ?? null,
                'stage_id'   => $job->job_stage_id ?? null,
                'completed'  => $job->completed_at ?? now()->toIso8601String(),
            ],
        );
    }
}
