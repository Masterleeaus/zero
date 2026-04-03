<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 9 — Repair Task
 *
 * An individual work item within a RepairOrder, optionally assigned
 * to a technician and tracked through a pending → completed lifecycle.
 */
class RepairTask extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'repair_tasks';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_order_id',
        'title',
        'description',
        'status',
        'sequence',
        'assigned_user_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'sequence'     => 'integer',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
