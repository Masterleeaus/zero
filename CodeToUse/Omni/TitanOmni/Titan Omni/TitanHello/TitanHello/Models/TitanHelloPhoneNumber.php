<?php

namespace Extensions\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class TitanHelloPhoneNumber extends Model
{
    protected $table = 'titan_hello_phone_numbers';

    protected $fillable = [
        'phone_number',
        'label',
        'is_active',
        'after_hours_mode',
        'forward_number',
        'agent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
