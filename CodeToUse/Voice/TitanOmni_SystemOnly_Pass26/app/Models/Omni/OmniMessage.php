<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmniMessage extends Model
{
    use HasFactory;

    protected $table = 'omni_messages';

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'agent_id',
        'message_type',
        'content',
        'role',
        'voice_file_url',
        'voice_duration_seconds',
        'voice_model',
        'voice_transcript',
        'voice_confidence',
        'media_url',
        'media_type',
        'media_size_bytes',
        'external_message_id',
        'read_at',
        'is_internal_note',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'voice_confidence' => 'float',
        'read_at' => 'datetime',
        'is_internal_note' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(OmniConversation::class, 'conversation_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }
}
