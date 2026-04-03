<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TzPwaStagedArtifact extends Model
{
    protected $table = 'tz_pwa_staged_artifacts';

    protected $fillable = [
        'company_id',
        'user_id',
        'node_id',
        'client_ref',
        'artifact_type',
        'artifact_stage',
        'job_id',
        'process_id',
        'signal_ref',
        'artifact_meta',
        'note_body',
        'filename',
        'mime_type',
        'file_size_bytes',
        'upload_attempted',
        'upload_complete',
        'upload_url',
        'reconciled_to_id',
        'reconciled_to_type',
        'reconciled_at',
        'retry_count',
        'failure_reason',
        'last_attempted_at',
        'client_captured_at',
    ];

    protected $casts = [
        'artifact_meta'       => 'array',
        'upload_attempted'    => 'boolean',
        'upload_complete'     => 'boolean',
        'reconciled_at'       => 'datetime',
        'last_attempted_at'   => 'datetime',
        'client_captured_at'  => 'datetime',
        'retry_count'         => 'integer',
        'file_size_bytes'     => 'integer',
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

    /** Scope: artifacts in a given stage */
    public function scopeInStage($query, string $stage)
    {
        return $query->where('artifact_stage', $stage);
    }

    /** Scope: artifacts pending reconciliation */
    public function scopePending($query)
    {
        return $query->where('artifact_stage', 'pending');
    }

    /** Scope: artifacts eligible for retry */
    public function scopeRetryable($query, int $maxRetries = 3)
    {
        return $query->whereIn('artifact_stage', ['pending', 'failed'])
            ->where('retry_count', '<', $maxRetries);
    }
}
