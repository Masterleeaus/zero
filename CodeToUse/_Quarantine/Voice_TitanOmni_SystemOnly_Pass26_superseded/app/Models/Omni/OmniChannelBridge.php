<?php

namespace App\Models\Omni;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmniChannelBridge extends Model
{
    use HasFactory;

    protected $table = 'omni_channel_bridges';

    protected $fillable = [
        'company_id',
        'agent_id',
        'channel',
        'bridge_driver',
        'bridge_key',
        'bridge_secret',
        'webhook_url',
        'is_active',
        'metadata',
    ];

    protected $hidden = ['bridge_secret'];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(OmniAgent::class, 'agent_id');
    }
}
