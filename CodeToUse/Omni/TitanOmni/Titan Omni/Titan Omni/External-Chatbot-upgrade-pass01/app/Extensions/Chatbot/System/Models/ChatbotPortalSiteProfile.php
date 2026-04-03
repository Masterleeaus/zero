<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotPortalSiteProfile extends Model
{
    protected $table = 'ext_chatbot_portal_site_profiles';

    protected $fillable = [
        'chatbot_id',
        'chatbot_customer_id',
        'company_id',
        'site_id',
        'site_label',
        'entry_method',
        'access_notes',
        'parking_notes',
        'pet_notes',
        'priority_rooms',
        'preferences',
        'media',
    ];

    protected $casts = [
        'preferences' => 'array',
        'media' => 'array',
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
