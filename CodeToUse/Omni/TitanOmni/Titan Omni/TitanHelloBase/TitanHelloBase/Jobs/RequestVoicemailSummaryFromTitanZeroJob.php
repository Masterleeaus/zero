<?php

namespace Modules\TitanHello\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\TitanHello\Models\Call;
use Modules\TitanHello\Models\CallRecording;
use Modules\TitanHello\Services\TitanZero\TitanZeroClient;

class RequestVoicemailSummaryFromTitanZeroJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $callId, public int $recordingId)
    {
    }

    public function handle(TitanZeroClient $client): void
    {
        $call = Call::find($this->callId);
        $rec = CallRecording::find($this->recordingId);
        if (!$call || !$rec) return;

        $res = $client->requestVoicemailSummary([
            'intent' => 'titanhello.voicemail.summarize',
            'company_id' => $call->company_id,
            'call_id' => $call->id,
            'recording_id' => $rec->id,
            'recording_path' => $rec->stored_path,
            'recording_disk' => $rec->disk,
            'from_number' => $call->from_number,
            'to_number' => $call->to_number,
        ]);

        // If Titan Zero returns artifact ids, store them. Fail silently.
        if (is_array($res)) {
            $call->voicemail_transcript_artifact_id = $res['transcript_artifact_id'] ?? $call->voicemail_transcript_artifact_id;
            $call->voicemail_summary_artifact_id = $res['summary_artifact_id'] ?? $call->voicemail_summary_artifact_id;
            $call->save();
        }
    }
}
