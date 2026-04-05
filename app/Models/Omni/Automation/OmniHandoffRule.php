<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Omni\OmniAgent;
use App\Models\Traits\HasOmniTenancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniHandoffRule — Rule that triggers transfer of a conversation to a human agent.
 *
 * When an inbound message or conversation event matches the rule's trigger,
 * the conversation is handed off to the specified user, team, or queue.
 *
 * Trigger types: keyword | sentiment | no_ai_response | escalation_requested | timeout
 *
 * Handoff target types: user | team | queue | null (first available)
 *
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string      $name
 * @property string      $trigger_type
 * @property array|null  $trigger_conditions
 * @property string|null $handoff_target_type
 * @property int|null    $handoff_target_id
 * @property string      $channel_scope
 * @property int         $priority
 * @property bool        $is_active
 * @property array|null  $metadata
 */
class OmniHandoffRule extends Model
{
    use BelongsToCompany;
    use HasOmniTenancy;

    protected $table = 'omni_handoff_rules';

    protected $fillable = [
        'company_id',
        'agent_id',
        'name',
        'trigger_type',
        'trigger_conditions',
        'handoff_target_type',
        'handoff_target_id',
        'channel_scope',
        'priority',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'is_active'          => 'boolean',
        'metadata'           => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    /**
     * Resolve the target user when handoff_target_type is 'user'.
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handoff_target_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTrigger(Builder $query, string $triggerType): Builder
    {
        return $query->where('trigger_type', $triggerType);
    }

    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where(function (Builder $q) use ($channel) {
            $q->where('channel_scope', 'all')->orWhere('channel_scope', $channel);
        });
    }

    /**
     * Ordered by priority descending (highest priority evaluated first).
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority');
    }

    /**
     * Eager-load agent + target user for rule evaluation (N+1 guard).
     */
    public function scopeWithContext(Builder $query): Builder
    {
        return $query->with(['agent', 'targetUser']);
    }
}
