<?php

declare(strict_types=1);

namespace App\Models\Sync;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records a conflict for a sync queue item.
 *
 * conflict_type values: field_collision|version_mismatch|deleted_subject|concurrent_edit
 * resolved_by values:   user|system|ai
 */
class EdgeSyncConflict extends Model
{
    use HasFactory;

    protected $table = 'edge_sync_conflicts';

    protected $fillable = [
        'sync_queue_id',
        'conflict_type',
        'server_state',
        'client_state',
        'resolved_by',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'server_state' => 'array',
        'client_state' => 'array',
        'resolution'   => 'array',
        'resolved_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function syncQueue(): BelongsTo
    {
        return $this->belongsTo(EdgeSyncQueue::class, 'sync_queue_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function resolve(string $resolvedBy, array $resolution): void
    {
        $this->update([
            'resolved_by' => $resolvedBy,
            'resolution'  => $resolution,
            'resolved_at' => now(),
        ]);
    }
}
