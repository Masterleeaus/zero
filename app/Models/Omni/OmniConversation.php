<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * OmniConversation — Unified conversation thread across all channels.
 *
 * Immutability notes:
 *   resolved_at — set once on close, never overwritten
 *   Routing history preserved via assigned_to changes (logged via OmniConversationTransferred event)
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property int|null    $omni_customer_id
 * @property int|null    $crm_customer_id
 * @property int|null    $linked_job_id
 * @property int|null    $linked_invoice_id
 * @property string|null $customer_name
 * @property string|null $customer_email
 * @property string|null $session_id
 * @property string      $channel_type
 * @property string|null $channel_id
 * @property string|null $external_conversation_id
 * @property string      $status
 * @property int|null    $assigned_to
 * @property bool        $is_pinned
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $resolved_at
 * @property \Carbon\Carbon|null $last_activity_at
 * @property int         $total_messages
 * @property array|null  $tags
 * @property array|null  $metadata
 */
class OmniConversation extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_conversations';

    protected $fillable = [
        'uuid',
        'company_id',
        'agent_id',
        'omni_customer_id',
        'crm_customer_id',
        'linked_job_id',
        'linked_invoice_id',
        'customer_name',
        'customer_email',
        'session_id',
        'channel_type',
        'channel_id',
        'external_conversation_id',
        'status',
        'assigned_to',
        'is_pinned',
        'started_at',
        'resolved_at',
        'last_activity_at',
        'total_messages',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'started_at'       => 'datetime',
        'resolved_at'      => 'datetime',
        'last_activity_at' => 'datetime',
        'tags'             => 'array',
        'metadata'         => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->started_at)) {
                $model->started_at = now();
            }
        });
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function omniCustomer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'omni_customer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OmniMessage::class, 'conversation_id');
    }

    public function voiceCalls(): HasMany
    {
        return $this->hasMany(Voice\OmniVoiceCall::class, 'conversation_id');
    }

    public function scopeOpen(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeResolved(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'resolved');
    }
}
