<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FSM Module 10 — Repair Template Part
 *
 * A parts list entry on a RepairTemplate, copied to RepairPartUsage on apply.
 */
class RepairTemplatePart extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'repair_template_parts';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_template_id',
        'part_name',
        'part_sku',
        'quantity',
        'unit_cost',
        'optional',
    ];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'optional'  => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(RepairTemplate::class, 'repair_template_id');
    }
}
