<?php

namespace Modules\TitanHello\Models\Voice;

use Illuminate\Database\Eloquent\Model;

class VoiceSession extends Model
{
    protected $table = 'titanhello_voice_sessions';

    protected $fillable = [
        'company_id','user_id','source','audio_disk','audio_path','audio_mime','utterance','status','inference_json',
    ];

    protected $casts = [
        'inference_json' => 'array',
    ];
}
