<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Traits\HasImmutableTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniMessageAttachment — Media/file attachment on an OmniMessage.
 *
 * Append-only: no updated_at column. Created alongside the parent message.
 * Part of the message's immutable evidence record.
 *
 * @property int         $id
 * @property int         $message_id
 * @property int         $company_id
 * @property string      $attachment_type
 * @property string      $media_url
 * @property string|null $media_type
 * @property int|null    $media_size_bytes
 * @property string|null $file_name
 * @property string|null $caption
 * @property string|null $external_media_id
 * @property array|null  $metadata
 * @property \Carbon\Carbon|null $created_at
 */
class OmniMessageAttachment extends Model
{
    use BelongsToCompany;
    use HasImmutableTimestamps;

    protected $table = 'omni_message_attachments';

    // No updated_at — append-only attachment record
    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'company_id',
        'attachment_type',
        'media_url',
        'media_type',
        'media_size_bytes',
        'file_name',
        'caption',
        'external_media_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'metadata'   => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(OmniMessage::class, 'message_id');
    }

    // ── Named scopes ─────────────────────────────────────────────────────────

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('attachment_type', $type);
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('attachment_type', 'image');
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('attachment_type', 'document');
    }
}
