<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TzPwaDevice extends Model
{
    protected $table = 'tz_pwa_devices';

    protected $fillable = [
        'node_id',
        'company_id',
        'user_id',
        'node_origin',
        'fingerprint',
        'signing_key',
        'trust_level',
        'device_label',
        'platform',
        'app_version',
        'meta_json',
        'capability_profile',
        'capability_tier',
        'runtime_version',
        'last_seen_at',
        'last_sync_at',
        'last_success_at',
        'signature_failures',
        'last_failure_at',
        'trust_demoted_at',
        'is_rate_limited',
        'trust_notes',
        'queue_backlog',
    ];

    protected $casts = [
        'meta_json'          => 'array',
        'capability_profile' => 'array',
        'last_seen_at'       => 'datetime',
        'last_sync_at'       => 'datetime',
        'last_success_at'    => 'datetime',
        'last_failure_at'    => 'datetime',
        'trust_demoted_at'   => 'datetime',
        'is_rate_limited'    => 'boolean',
        'signature_failures' => 'integer',
        'queue_backlog'      => 'integer',
    ];

    protected $hidden = ['signing_key'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signalIngresses(): HasMany
    {
        return $this->hasMany(TzPwaSignalIngress::class, 'node_id', 'node_id');
    }

    public function stagedArtifacts(): HasMany
    {
        return $this->hasMany(TzPwaStagedArtifact::class, 'node_id', 'node_id');
    }
}
