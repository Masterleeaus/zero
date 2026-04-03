<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class CallEvent extends Model
{
    protected $table = 'titanhello_call_events';

    protected $fillable = [
        'call_id',
        'event_type',
        'event_name',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
        'call_id' => 'integer',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }
}
