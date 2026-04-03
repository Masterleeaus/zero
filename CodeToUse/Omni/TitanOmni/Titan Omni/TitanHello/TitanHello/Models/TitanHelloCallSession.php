<?php

namespace Extensions\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitanHelloCallSession extends Model
{
    protected $table = 'titan_hello_call_sessions';

    protected $fillable = [
        'call_sid',
        'from_number',
        'to_number',
        'direction',
        'status',
        'phone_number_id',
        'agent_id',
        'started_at',
        'ended_at',
        'recording_enabled',
        'recording_url',
        'summary',
        'meta',
    ];

    protected $casts = [
        'recording_enabled' => 'boolean',
        'meta' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(TitanHelloCallEvent::class, 'call_session_id');
    }

    public function phoneNumber(): BelongsTo
    {
        return $this->belongsTo(TitanHelloPhoneNumber::class, 'phone_number_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(ExtVoiceChatbot::class, 'agent_id');
    }
}
