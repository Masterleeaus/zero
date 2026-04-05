<?php

declare(strict_types=1);

namespace App\Models\Omni;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * OmniChannelBridge — Per-company channel credential and webhook configuration.
 *
 * Security note: credentials column must be encrypted at rest via application layer.
 * No plaintext API keys may be stored in this column.
 *
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $agent_id
 * @property string      $channel_type
 * @property string|null $bridge_driver
 * @property string|null $credentials
 * @property string|null $webhook_url
 * @property string|null $webhook_secret
 * @property bool        $is_active
 * @property \Carbon\Carbon|null $verified_at
 * @property array|null  $metadata
 */
class OmniChannelBridge extends Model
{
    use BelongsToCompany;

    protected $table = 'omni_channel_bridges';

    protected $fillable = [
        'company_id',
        'agent_id',
        'channel_type',
        'bridge_driver',
        'credentials',
        'webhook_url',
        'webhook_secret',
        'is_active',
        'verified_at',
        'metadata',
    ];

    protected $hidden = ['credentials', 'webhook_secret'];

    protected $casts = [
        'is_active'   => 'boolean',
        'verified_at' => 'datetime',
        'metadata'    => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }

    public function voiceCalls(): HasMany
    {
        return $this->hasMany(Voice\OmniVoiceCall::class, 'channel_bridge_id');
    }
}
