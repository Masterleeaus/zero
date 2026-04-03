<?php

namespace Extensions\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class TitanHelloLead extends Model
{
    protected $table = 'titan_hello_leads';

    protected $fillable = [
        'call_session_id',
        'caller_name',
        'caller_phone',
        'suburb',
        'job_type',
        'urgency',
        'callback_window',
        'notes',
    ];
}
