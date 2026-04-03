<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class CallRecording extends Model
{
    protected $table = 'titanhello_call_recordings';

    protected $fillable = [
        'call_id',
        'provider',
        'provider_recording_sid',
        'recording_url',
        'kind',
        'fetched_at',
        'fetch_status',
        'fetch_error',
        'bytes',
        'sha256',
        'disk',
        'duration_seconds',
        'content_type',
        'stored_path',
        'available_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'fetched_at' => 'datetime',
        'duration_seconds' => 'integer',
        'call_id' => 'integer',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }
}
