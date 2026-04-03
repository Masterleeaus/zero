<?php

namespace Modules\TitanHello\Services\Recordings;

use Illuminate\Support\Facades\Storage;
use Modules\TitanHello\Models\CallRecording;

class RecordingRetentionService
{
    public function prune(int $retentionDays): int
    {
        $cutoff = now()->subDays($retentionDays);

        $recs = CallRecording::query()
            ->whereNotNull('stored_path')
            ->where('created_at', '<', $cutoff)
            ->limit(500)
            ->get();

        $deleted = 0;
        foreach ($recs as $rec) {
            $disk = $rec->disk ?: config('titanhello.recordings.disk', 'local');
            try {
                if ($rec->stored_path) {
                    Storage::disk($disk)->delete($rec->stored_path);
                }
            } catch (\Throwable $e) {
                // ignore delete failures; still mark pruned
            }

            $rec->stored_path = null;
            $rec->fetch_status = 'pruned';
            $rec->save();
            $deleted++;
        }

        return $deleted;
    }
}
