<?php

declare(strict_types=1);

namespace App\Models\Sync;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Represents a single offline operation queued for sync.
 *
 * operation_type values: job_update|checklist_response|inspection_response|
 *                        evidence_upload|signature_capture|job_complete
 * status values: pending|processing|synced|conflict|failed
 */
class EdgeSyncQueue extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'edge_sync_queues';

    protected $fillable = [
        'company_id',
        'device_id',
        'user_id',
        'operation_type',
        'subject_type',
        'subject_id',
        'payload',
        'client_created_at',
        'status',
        'attempts',
        'last_attempt_at',
        'error_message',
    ];

    protected $casts = [
        'payload'           => 'array',
        'client_created_at' => 'datetime',
        'last_attempt_at'   => 'datetime',
        'attempts'          => 'integer',
    ];

    protected $attributes = [
        'status'   => 'pending',
        'attempts' => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conflict(): HasOne
    {
        return $this->hasOne(EdgeSyncConflict::class, 'sync_queue_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConflict(): bool
    {
        return $this->status === 'conflict';
    }

    public function isSynced(): bool
    {
        return $this->status === 'synced';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markProcessing(): void
    {
        $this->update([
            'status'          => 'processing',
            'last_attempt_at' => now(),
            'attempts'        => $this->attempts + 1,
        ]);
    }

    public function markSynced(): void
    {
        $this->update(['status' => 'synced']);
    }

    public function markConflict(): void
    {
        $this->update(['status' => 'conflict']);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $error,
        ]);
    }
}
