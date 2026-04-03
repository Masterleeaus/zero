<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotPortalNotification extends Model
{
    protected $table = 'ext_chatbot_portal_notifications';

    protected $fillable = [
        'chatbot_id',
        'chatbot_customer_id',
        'company_id',
        'type',
        'title',
        'body',
        'action_label',
        'action_url',
        'payload',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ChatbotCustomer::class, 'chatbot_customer_id');
    }
}
