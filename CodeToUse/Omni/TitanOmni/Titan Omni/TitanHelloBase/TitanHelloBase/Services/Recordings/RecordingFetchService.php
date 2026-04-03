<?php

namespace Modules\TitanHello\Services\Recordings;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\TitanHello\Models\CallRecording;

class RecordingFetchService
{
    public function fetch(CallRecording $rec): CallRecording
    {
        $url = (string)($rec->recording_url ?? '');
        if (!$url) {
            $rec->fetch_status = 'failed';
            $rec->fetch_error = 'Missing recording_url';
            $rec->save();
            return $rec;
        }

        $disk = config('titanhello.recordings.disk', 'local');
        $dir = trim((string)config('titanhello.recordings.path', 'titanhello/recordings'), '/');
        $ext = 'mp3';

        // Twilio RecordingUrl is often without extension; appending .mp3 returns audio.
        $downloadUrl = $url;
        if (!preg_match('/\.(mp3|wav)($|\?)/i', $downloadUrl)) {
            $downloadUrl = rtrim($downloadUrl, '/') . '.mp3';
        }

        $authUser = config('titanhello.twilio.account_sid');
        $authPass = config('titanhello.twilio.auth_token');

        $resp = Http::timeout(30)->withBasicAuth((string)$authUser, (string)$authPass)->get($downloadUrl);
        if (!$resp->ok()) {
            $rec->fetch_status = 'failed';
            $rec->fetch_error = 'HTTP ' . $resp->status();
            $rec->save();
            return $rec;
        }

        $bytes = $resp->body();
        $sha = hash('sha256', $bytes);
        $filename = ($rec->provider_recording_sid ?: ('rec_' . $rec->id)) . '.' . $ext;
        $path = $dir . '/' . $filename;

        Storage::disk($disk)->put($path, $bytes);

        $rec->stored_path = $path;
        $rec->disk = $disk;
        $rec->bytes = strlen($bytes);
        $rec->sha256 = $sha;
        $rec->fetched_at = now();
        $rec->fetch_status = 'ok';
        $rec->fetch_error = null;
        $rec->save();

        return $rec;
    }
}
