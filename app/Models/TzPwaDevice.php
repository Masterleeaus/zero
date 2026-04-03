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
        'platform',
        'trust_level',
        'last_seen_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_seen_at' => 'datetime',
    ];

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
}
