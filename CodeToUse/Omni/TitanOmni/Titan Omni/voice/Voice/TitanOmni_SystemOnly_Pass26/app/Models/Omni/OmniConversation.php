<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OmniConversation extends Model
{
    use HasFactory;

    protected $table = 'omni_conversations';

    protected $fillable = [
        'company_id',
        'uuid',
        'agent_id',
        'customer_id',
        'customer_email',
        'customer_name',
        'session_id',
        'channel_type',
        'channel_id',
        'external_conversation_id',
        'status',
        'assigned_user_id',
        'is_pinned',
        'last_activity_at',
        'total_messages',
        'user_messages',
        'assistant_messages',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'last_activity_at' => 'datetime',
        'tags' => 'array',
        'metadata' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OmniMessage::class, 'conversation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'customer_id');
    }

    public function voiceCalls(): HasMany
    {
        return $this->hasMany(OmniVoiceCall::class, 'conversation_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
