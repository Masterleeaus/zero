<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotPortalRecurringService extends Model
{
    protected $table = 'ext_chatbot_portal_recurring_services';

    protected $fillable = [
        'chatbot_id',
        'chatbot_customer_id',
        'company_id',
        'site_id',
        'quote_id',
        'project_id',
        'service_name',
        'frequency',
        'next_service_date',
        'is_paused',
        'paused_until',
        'extras',
        'rules',
    ];

    protected $casts = [
        'next_service_date' => 'date',
        'is_paused' => 'boolean',
        'paused_until' => 'date',
        'extras' => 'array',
        'rules' => 'array',
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
