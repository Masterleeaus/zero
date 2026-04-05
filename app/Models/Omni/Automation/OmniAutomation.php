<?php

declare(strict_types=1);

namespace App\Models\Omni\Automation;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Omni\OmniAgent;
use App\Models\Traits\HasOmniTenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OmniAutomation — Trigger-based automation rule.
 *
 * An automation watches for a trigger event and executes one or more
 * OmniAutomationActions in order when the trigger conditions are met.
 *
 * Trigger types: message_received | conversation_started | conversation_resolved
 *                keyword_match | no_reply | campaign_delivered | webhook
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string      $name
 * @property string|null $description
 * @property string      $trigger_type
 * @property array|null  $trigger_conditions
 * @property string      $channel_scope
 * @property bool        $is_active
 * @property int         $run_count
 * @property array|null  $metadata
 */
class OmniAutomation extends Model
{
    use BelongsToCompany;
    use HasOmniTenancy;

    protected $table = 'omni_automations';

    protected $fillable = [
        'uuid',
        'company_id',
        'agent_id',
        'name',
        'description',
        'trigger_type',
        'trigger_conditions',
        'channel_scope',
        'is_active',
        'run_count',
        'metadata',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'is_active'          => 'boolean',
        'metadata'           => 'array',
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

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(OmniAutomationAction::class, 'automation_id')->orderBy('action_order');
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
     * Eager-load automation actions for execution (N+1 guard).
     */
    public function scopeWithActions(Builder $query): Builder
    {
        return $query->with(['actions']);
    }
}
