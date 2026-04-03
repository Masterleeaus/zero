<?php

namespace Modules\TitanHello\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\TitanHello\Services\Recordings\RecordingRetentionService;

class PruneOldRecordingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $retentionDays)
    {
    }

    public function handle(RecordingRetentionService $svc): void
    {
        $svc->prune($this->retentionDays);
    }
}
