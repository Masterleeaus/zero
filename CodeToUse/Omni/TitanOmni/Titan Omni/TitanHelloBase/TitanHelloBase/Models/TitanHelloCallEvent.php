<?php

namespace Extensions\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitanHelloCallEvent extends Model
{
    protected $table = 'titan_hello_call_events';

    protected $fillable = [
        'call_session_id',
        'type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function callSession(): BelongsTo
    {
        return $this->belongsTo(TitanHelloCallSession::class, 'call_session_id');
    }
}
