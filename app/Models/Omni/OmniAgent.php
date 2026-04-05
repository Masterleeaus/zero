<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OmniAgent — AI agent definition per company.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $user_id
 * @property string      $name
 * @property string      $slug
 * @property string      $role
 * @property string      $model
 * @property string|null $avatar_url
 * @property string|null $instructions
 * @property string|null $system_prompt
 * @property string      $tone
 * @property string      $language
 * @property string      $channel_scope
 * @property bool        $is_active
 * @property bool        $is_favorite
 * @property array|null  $metadata
 */
class OmniAgent extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_agents';

    protected $fillable = [
        'uuid',
        'company_id',
        'user_id',
        'name',
        'slug',
        'role',
        'model',
        'avatar_url',
        'instructions',
        'system_prompt',
        'tone',
        'language',
        'channel_scope',
        'is_active',
        'is_favorite',
        'metadata',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_favorite' => 'boolean',
        'metadata'    => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

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
