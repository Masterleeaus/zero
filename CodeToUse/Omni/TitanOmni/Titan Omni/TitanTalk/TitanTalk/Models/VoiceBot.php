<?php
namespace Modules\TitanTalk\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceBot extends Model
{
    protected $table = 'ai_converse_voice_bots';

    protected $fillable = [
        'name',
        'provider',
        'external_id',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings'  => 'array',
        'is_active' => 'boolean',
    ];
}
