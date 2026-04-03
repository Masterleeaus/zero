<?php

namespace App\Extensions\ChatbotWhatsapp\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineVoiceAction extends Model
{
    use HasFactory;

    protected $table = 'ext_chatbot_offline_voice_actions';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];
}
