<?php

namespace Modules\TitanHello\Models\Voice;

use Illuminate\Database\Eloquent\Model;

class VoiceCall extends Model
{
    protected $table = 'titanhello_voice_calls';

    protected $fillable = [
        'provider','call_sid','from','to','direction','status','recording_url','recording_duration','raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
