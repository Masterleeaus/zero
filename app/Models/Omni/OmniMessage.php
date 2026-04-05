<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * OmniMessage — Individual message within a conversation.
 *
 * IMMUTABLE after id is assigned.
 *   - No soft-deletes
 *   - No UPDATE after delivered_at is set
 *   - No updated_at column
 *   - delivered_at, read_at, failed_at set once and never overwritten
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $conversation_id
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string      $direction
 * @property string      $content_type
 * @property string|null $content
 * @property string      $sender_type
 * @property int|null    $sender_id
 * @property \Carbon\Carbon|null $delivered_at
 * @property \Carbon\Carbon|null $read_at
 * @property \Carbon\Carbon|null $failed_at
 * @property string|null $failure_reason
 * @property string|null $media_url
 * @property string|null $media_type
 * @property int|null    $media_size_bytes
 * @property string|null $voice_file_url
 * @property int|null    $voice_duration_seconds
 * @property string|null $voice_transcript
 * @property string|null $external_message_id
 * @property bool        $is_internal_note
 * @property array|null  $metadata
 * @property \Carbon\Carbon|null $created_at
 */
class OmniMessage extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_messages';

    // No updated_at — immutable record
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'company_id',
        'agent_id',
        'direction',
        'content_type',
        'content',
        'sender_type',
        'sender_id',
        'delivered_at',
        'read_at',
        'failed_at',
        'failure_reason',
        'media_url',
        'media_type',
        'media_size_bytes',
        'voice_file_url',
        'voice_duration_seconds',
        'voice_transcript',
        'external_message_id',
        'is_internal_note',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
        'delivered_at'     => 'datetime',
        'read_at'          => 'datetime',
        'failed_at'        => 'datetime',
        'created_at'       => 'datetime',
        'metadata'         => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(OmniConversation::class, 'conversation_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }
}
