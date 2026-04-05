<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Omni\Automation\OmniAutomation;
use App\Models\Omni\Automation\OmniHandoffRule;
use App\Models\Omni\Automation\OmniSequence;
use App\Models\Traits\HasOmniTenancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    use HasOmniTenancy;

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

    // ── Host relationships ───────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Omni relationships ───────────────────────────────────────────────────

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

    public function sequences(): HasMany
    {
        return $this->hasMany(OmniSequence::class, 'agent_id');
    }

    public function automations(): HasMany
    {
        return $this->hasMany(OmniAutomation::class, 'agent_id');
    }

    public function handoffRules(): HasMany
    {
        return $this->hasMany(OmniHandoffRule::class, 'agent_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where(function (Builder $q) use ($channel) {
            $q->where('channel_scope', 'all')->orWhere('channel_scope', $channel);
        });
    }

    /**
     * Eager-load commonly needed agent context (N+1 guard).
     */
    public function scopeWithContext(Builder $query): Builder
    {
        return $query->with(['user', 'channelBridges']);
    }
}
