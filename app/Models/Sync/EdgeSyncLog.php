<?php

declare(strict_types=1);

namespace App\Models\Sync;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log entry for each sync batch.
 *
 * One record per batch_id (uuid), recording counts and timing.
 */
class EdgeSyncLog extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'edge_sync_log';

    protected $fillable = [
        'company_id',
        'user_id',
        'device_id',
        'batch_id',
        'operations_count',
        'conflicts_count',
        'failed_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'operations_count' => 'integer',
        'conflicts_count'  => 'integer',
        'failed_count'     => 'integer',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function complete(int $conflictsCount, int $failedCount): void
    {
        $this->update([
            'conflicts_count' => $conflictsCount,
            'failed_count'    => $failedCount,
            'completed_at'    => now(),
        ]);
    }
}
