<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Inspection\InspectionTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links a ChecklistTemplate or InspectionTemplate to a ServicePlan.
 *
 * Templates listed here are automatically injected into each visit /
 * ServiceJob generated from this plan.
 */
class ServicePlanChecklist extends Model
{
    protected $table = 'service_plan_checklists';

    protected $fillable = [
        'service_plan_id',
        'checklist_template_id',
        'inspection_template_id',
        'label',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active'  => true,
        'sort_order' => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function inspectionTemplate(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }
}
