<?php

namespace Modules\TitanHello\Models;

use Illuminate\Database\Eloquent\Model;

class InboundNumber extends Model
{
    protected $table = 'titanhello_inbound_numbers';

    protected $fillable = [
        'company_id',
        'phone_number',
        'label',
        'mode',
        'target_id',
        'enabled',
        'business_hours_only',
        'after_hours_mode',
        'after_hours_target_id',
        'business_hours_json',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'business_hours_only' => 'boolean',
        'target_id' => 'integer',
        'after_hours_target_id' => 'integer',
        'business_hours_json' => 'array',
    ];
}
