<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $table = 'titanhello_calls';

    protected $fillable = [
        'company_id',
        'direction',
        'provider',
        'provider_call_sid',
        'from_number',
        'to_number',
        'status',
        'answered_at',
        'ended_at',
        'duration_seconds',
        'recording_enabled',
        'assigned_to_user_id',
        'disposition',
        'disposition_notes',
        'priority',
        'callback_due_at',
        'missed_at',
        'assigned_at',
        'last_event_at',
        'voicemail_flag',
        'voicemail_received_at',
        'voicemail_recording_id',
        'voicemail_transcript_artifact_id',
        'voicemail_summary_artifact_id',
        'meta',
        'call_outcome',
        'ring_duration',
        'missed_reason',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'ended_at' => 'datetime',
        'recording_enabled' => 'boolean',
        'duration_seconds' => 'integer',
        'company_id' => 'integer',
        'assigned_to_user_id' => 'integer',
        'voicemail_flag' => 'boolean',
        'voicemail_received_at' => 'datetime',
        'meta' => 'array',
    ];

    public function events()
    {
        return $this->hasMany(CallEvent::class, 'call_id')->orderBy('id');
    }

    public function recordings()
    {
        return $this->hasMany(CallRecording::class, 'call_id')->orderBy('id');
    }

    public function notes()
    {
        return $this->hasMany(CallNote::class, 'call_id')->orderByDesc('id');
    }

}
