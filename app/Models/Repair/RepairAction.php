<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FSM Module 9 — Repair Action
 *
 * Records discrete actions taken during a repair (part replaced,
 * diagnostic step completed, etc.), optionally linked to a RepairTask.
 */
class RepairAction extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'repair_actions';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_order_id',
        'repair_task_id',
        'action_type',
        'description',
        'performed_by',
        'performed_at',
        'duration_minutes',
    ];

    protected $casts = [
        'performed_at'     => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function repairTask(): BelongsTo
    {
        return $this->belongsTo(RepairTask::class, 'repair_task_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
