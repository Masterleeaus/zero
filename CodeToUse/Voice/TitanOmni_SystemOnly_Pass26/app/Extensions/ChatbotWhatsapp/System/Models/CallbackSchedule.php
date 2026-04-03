<?php

namespace App\Extensions\ChatbotWhatsapp\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallbackSchedule extends Model
{
    use HasFactory;

    protected $table = 'ext_chatbot_callback_schedules';

    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'metadata' => 'array',
    ];
}
