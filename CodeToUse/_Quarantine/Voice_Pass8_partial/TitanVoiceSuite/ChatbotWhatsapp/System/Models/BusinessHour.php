<?php

namespace App\Extensions\ChatbotWhatsapp\System\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $table = 'ext_chatbot_business_hours';

    protected $guarded = [];

    protected $casts = [
        'is_holiday' => 'boolean',
    ];
}
