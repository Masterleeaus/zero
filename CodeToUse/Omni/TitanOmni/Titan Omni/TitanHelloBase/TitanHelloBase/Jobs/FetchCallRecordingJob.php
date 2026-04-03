<?php

namespace Modules\TitanHello\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\TitanHello\Models\CallRecording;
use Modules\TitanHello\Services\Recordings\RecordingFetchService;

class FetchCallRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $recordingId)
    {
    }

    public function handle(RecordingFetchService $fetcher): void
    {
        $rec = CallRecording::find($this->recordingId);
        if (!$rec) return;

        $fetcher->fetch($rec);
    }
}
