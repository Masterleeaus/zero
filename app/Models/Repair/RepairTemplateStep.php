<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FSM Module 10 — Repair Template Step
 *
 * An ordered procedural step within a RepairTemplate.
 */
class RepairTemplateStep extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'repair_template_steps';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_template_id',
        'title',
        'description',
        'step_type',
        'sequence',
        'estimated_duration',
        'requires_parts',
        'safety_flag',
    ];

    protected $casts = [
        'requires_parts'     => 'boolean',
        'safety_flag'        => 'boolean',
        'sequence'           => 'integer',
        'estimated_duration' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(RepairTemplate::class, 'repair_template_id');
    }
}
