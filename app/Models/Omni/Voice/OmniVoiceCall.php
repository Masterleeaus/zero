<?php

declare(strict_types=1);

namespace App\Models\Omni\Voice;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Omni\OmniConversation;
use App\Models\Omni\OmniCustomer;
use App\Models\Omni\OmniChannelBridge;
use Illuminate\Support\Str;

/**
 * OmniVoiceCall — Voice call record (inbound + outbound).
 *
 * Immutability contract:
 *   started_at — set once when call begins, never overwritten
 *   ended_at   — set once when call completes, never overwritten
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $company_id
 * @property int|null    $conversation_id
 * @property int|null    $channel_bridge_id
 * @property int|null    $omni_customer_id
 * @property string      $direction
 * @property string      $provider
 * @property string|null $provider_call_id
 * @property string|null $from_number
 * @property string|null $to_number
 * @property string      $status
 * @property int|null    $duration_seconds
 * @property string|null $recording_url
 * @property string|null $transcript
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $ended_at
 * @property array|null  $metadata
 */
class OmniVoiceCall extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_voice_calls';

    protected $fillable = [
        'uuid',
        'company_id',
        'conversation_id',
        'channel_bridge_id',
        'omni_customer_id',
        'direction',
        'provider',
        'provider_call_id',
        'from_number',
        'to_number',
        'status',
        'duration_seconds',
        'recording_url',
        'transcript',
        'started_at',
        'ended_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'metadata'   => 'array',
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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(OmniConversation::class, 'conversation_id');
    }

    public function omniCustomer(): BelongsTo
    {
        return $this->belongsTo(OmniCustomer::class, 'omni_customer_id');
    }

    public function channelBridge(): BelongsTo
    {
        return $this->belongsTo(OmniChannelBridge::class, 'channel_bridge_id');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(OmniCallLog::class, 'voice_call_id');
    }
}
