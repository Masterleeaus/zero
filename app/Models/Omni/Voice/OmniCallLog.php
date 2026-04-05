<?php

declare(strict_types=1);

namespace App\Models\Omni\Voice;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Traits\HasImmutableTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OmniCallLog — Timestamped voice call event (append-only).
 *
 * APPEND-ONLY: No UPDATE, no DELETE permitted by application contract.
 * No timestamps columns — occurred_at is the sole temporal anchor.
 *
 * @property int         $id
 * @property int         $voice_call_id
 * @property int         $company_id
 * @property string      $event_type
 * @property array|null  $payload
 * @property \Carbon\Carbon|null $occurred_at
 */
class OmniCallLog extends Model
{
    use BelongsToCompany;
    use HasImmutableTimestamps;

    protected $table = 'omni_call_logs';

    // Append-only — no Laravel timestamp columns
    public $timestamps = false;

    protected $fillable = [
        'voice_call_id',
        'company_id',
        'event_type',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
    ];

    public function voiceCall(): BelongsTo
    {
        return $this->belongsTo(OmniVoiceCall::class, 'voice_call_id');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }
}
