<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmniVoiceCall extends Model
{
    use HasFactory;

    protected $table = 'omni_voice_calls';

    protected $fillable = [
        'company_id',
        'conversation_id',
        'channel_bridge_id',
        'call_sid',
        'from_number',
        'to_number',
        'status',
        'duration_seconds',
        'recording_url',
        'transcript',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(OmniConversation::class, 'conversation_id');
    }

    public function bridge(): BelongsTo
    {
        return $this->belongsTo(OmniChannelBridge::class, 'channel_bridge_id');
    }
}
