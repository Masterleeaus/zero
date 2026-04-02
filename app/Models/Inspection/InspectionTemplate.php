<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Reusable inspection blueprint.
 *
 * Defines the type, structure, and items of an inspection.
 * InspectionSchedules and InspectionInstances reference a template.
 *
 * Inspection types: routine | safety | exit | entry | compliance | qa
 */
class InspectionTemplate extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'inspection_templates';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'inspection_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(InspectionTemplateItem::class, 'inspection_template_id')
            ->orderBy('sort_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(InspectionSchedule::class, 'inspection_template_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(InspectionInstance::class, 'inspection_template_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
