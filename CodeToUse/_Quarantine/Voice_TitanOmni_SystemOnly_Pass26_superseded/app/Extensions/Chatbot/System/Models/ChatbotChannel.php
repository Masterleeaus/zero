<?php

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotChannel extends Model
{
    protected $table = 'ext_chatbot_channels';

    protected $fillable = [
        'user_id',
        'chatbot_id',
        'channel',
        'credentials',
        'payload',
        'connected_at',
        'team_id',
        'company_id',
    ];

    protected $casts = [
        'credentials'  => 'json',
        'payload'      => 'json',
        'connected_at' => 'datetime',
        'team_id'      => 'integer',
        'company_id'   => 'integer',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(ChatbotChannelWebhook::class, 'chatbot_channel_id');
    }

    public function isSandbox(): bool
    {
        return data_get($this->credentials, 'whatsapp_environment') === 'sandbox';
    }

    public function credential(string $key, mixed $default = null): mixed
    {
        return data_get($this->credentials, $key, $default);
    }

    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->payload, $key, $default);
    }
}

