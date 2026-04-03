<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 9 — Repair Checklist
 *
 * A completion-tracked checklist run against a RepairOrder,
 * independent of the InspectionInstance/ChecklistRun systems.
 */
class RepairChecklist extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'repair_checklists';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_order_id',
        'title',
        'checklist_type',
        'status',
        'items_total',
        'items_completed',
        'items_failed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'items_total'     => 'integer',
        'items_completed' => 'integer',
        'items_failed'    => 'integer',
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function completionPercentage(): float
    {
        if ($this->items_total === 0) {
            return 0.0;
        }

        return round(($this->items_completed / $this->items_total) * 100, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
