<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TzPwaSignalIngress extends Model
{
    protected $table = 'tz_pwa_signal_ingress';

    protected $fillable = [
        'node_id',
        'idempotency_key',
        'company_id',
        'user_id',
        'signal_key',
        'payload',
        'signature',
        'timestamp',
        'signal_stage',
        'ingest_status',
        'failure_reason',
        'consensus_score',
        'consensus_passed',
        'envelope',
        'meta',
        'processed_at',
        'promoted_to_event_id',
    ];

    protected $casts = [
        'payload'          => 'array',
        'envelope'         => 'array',
        'meta'             => 'array',
        'consensus_passed' => 'boolean',
        'consensus_score'  => 'float',
        'timestamp'        => 'datetime',
        'processed_at'     => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(TzPwaDevice::class, 'node_id', 'node_id');
    }
}
