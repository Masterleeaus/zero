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
 * FSM Module 9 — Repair Part Usage
 *
 * Tracks parts reserved or consumed against a RepairOrder,
 * with optional linkage to an inventory part record.
 */
class RepairPartUsage extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'repair_part_usages';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_order_id',
        'part_id',
        'part_name',
        'part_sku',
        'quantity',
        'unit_cost',
        'stock_location',
        'movement_reference',
        'reserved',
        'consumed',
        'supplier_reference',
    ];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'reserved'  => 'boolean',
        'consumed'  => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isReserved(): bool
    {
        return (bool) $this->reserved;
    }

    public function isConsumed(): bool
    {
        return (bool) $this->consumed;
    }

    public function totalCost(): float
    {
        return (float) ($this->unit_cost ?? 0) * (float) ($this->quantity ?? 1);
    }
}
