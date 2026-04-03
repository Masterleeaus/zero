<?php

namespace App\Extensions\ChatbotWhatsapp\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    protected $table = 'ext_chatbot_call_logs';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
