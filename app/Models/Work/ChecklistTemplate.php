<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Reusable checklist template.
 *
 * A named template of items that can be executed (as a ChecklistRun) in the
 * context of a ServiceJob, InspectionInstance, or Premises.
 *
 * Categories: safety | hygiene | inspection | handover | maintenance | custom
 */
class ChecklistTemplate extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'checklist_templates';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'category',
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
        return $this->hasMany(ChecklistItem::class, 'checklist_template_id')
            ->orderBy('sort_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ChecklistRun::class, 'checklist_template_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
