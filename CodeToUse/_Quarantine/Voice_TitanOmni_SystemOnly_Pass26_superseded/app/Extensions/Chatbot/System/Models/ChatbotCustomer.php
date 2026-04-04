<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotCustomer extends Model
{
    protected $table = 'ext_chatbot_customers';

    protected $fillable = [
        'user_id',
        'avatar',
        'name',
        'email',
        'phone',
        'chatbot_id',
        'session_id',
        'country_code',
        'ip_address',
        'chatbot_channel',
        'payload',
        'team_id',
        'company_id',
    ];

    protected $casts = [
        'payload' => 'json',
        'team_id' => 'integer',
        'company_id' => 'integer',
    ];
}
