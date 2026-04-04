<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OmniAgent extends Model
{
    use HasFactory;

    protected $table = 'omni_agents';

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'slug',
        'channel_scope',
        'persona',
        'language',
        'is_active',
        'is_favorite',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_favorite' => 'boolean',
        'metadata' => 'array',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(OmniConversation::class, 'agent_id');
    }

    public function channelBridges(): HasMany
    {
        return $this->hasMany(OmniChannelBridge::class, 'agent_id');
    }

    public function knowledgeArticles(): HasMany
    {
        return $this->hasMany(OmniKnowledgeArticle::class, 'agent_id');
    }
}
