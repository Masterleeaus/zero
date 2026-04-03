<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FSM Module 10 — Repair Template Checklist
 *
 * A checklist template attached to a RepairTemplate, used to spawn
 * RepairChecklist records when the template is applied to a RepairOrder.
 */
class RepairTemplateChecklist extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'repair_template_checklists';

    protected $fillable = [
        'company_id',
        'created_by',
        'repair_template_id',
        'title',
        'checklist_type',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(RepairTemplate::class, 'repair_template_id');
    }
}
