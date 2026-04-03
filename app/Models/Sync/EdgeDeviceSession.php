<?php

declare(strict_types=1);

namespace App\Models\Sync;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks a registered device session for offline/edge sync.
 *
 * device_id is a client-generated UUID, unique per user (not globally).
 * platform values: ios|android|web|pwa
 */
class EdgeDeviceSession extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'edge_device_sessions';

    protected $fillable = [
        'company_id',
        'user_id',
        'device_id',
        'device_name',
        'platform',
        'last_sync_at',
        'sync_cursor',
        'is_active',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'sync_cursor'  => 'integer',
        'is_active'    => 'boolean',
    ];

    protected $attributes = [
        'platform'    => 'pwa',
        'sync_cursor' => 0,
        'is_active'   => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function advanceCursor(int $cursor): void
    {
        if ($cursor > $this->sync_cursor) {
            $this->update([
                'sync_cursor'  => $cursor,
                'last_sync_at' => now(),
            ]);
        }
    }

    public function touch(): static
    {
        $this->update(['last_sync_at' => now()]);

        return $this;
    }
}
