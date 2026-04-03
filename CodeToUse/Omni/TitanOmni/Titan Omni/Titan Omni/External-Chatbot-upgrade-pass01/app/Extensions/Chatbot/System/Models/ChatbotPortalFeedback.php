<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotPortalFeedback extends Model
{
    protected $table = 'ext_chatbot_portal_feedback';

    protected $fillable = [
        'chatbot_id',
        'chatbot_customer_id',
        'conversation_id',
        'site_id',
        'project_id',
        'invoice_id',
        'ticket_id',
        'rating',
        'status',
        'is_reclean_request',
        'feedback',
        'attachments',
        'resolution_events',
        'resolved_at',
    ];

    protected $casts = [
        'is_reclean_request' => 'boolean',
        'attachments' => 'array',
        'resolution_events' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ChatbotCustomer::class, 'chatbot_customer_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'conversation_id');
    }
}
