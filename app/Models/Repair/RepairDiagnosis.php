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
 * FSM Module 9 — Repair Diagnosis
 *
 * Records symptom analysis, root cause identification, and
 * recommended actions for a RepairOrder.
 */
class RepairDiagnosis extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'repair_diagnoses';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_order_id',
        'symptom',
        'cause',
        'recommended_action',
        'safety_flag',
        'requires_specialist',
        'requires_parts',
        'requires_quote',
        'estimated_duration',
        'estimated_cost',
    ];

    protected $casts = [
        'safety_flag'          => 'boolean',
        'requires_specialist'  => 'boolean',
        'requires_parts'       => 'boolean',
        'requires_quote'       => 'boolean',
        'estimated_cost'       => 'decimal:2',
        'estimated_duration'   => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function repairOrder(): BelongsTo
    {
        return $this->belongsTo(RepairOrder::class);
    }
}
