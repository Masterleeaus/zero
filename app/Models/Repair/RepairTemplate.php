<?php

declare(strict_types=1);

namespace App\Models\Repair;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Services\Repair\RepairTemplateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 10 — Repair Template Engine
 *
 * Defines reusable repair procedure templates that can be applied to
 * RepairOrders to pre-populate tasks, parts, and checklists.
 */
class RepairTemplate extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'repair_templates';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'template_category',
        'equipment_type',
        'fault_type',
        'manufacturer',
        'service_category',
        'estimated_duration',
        'safety_notes',
        'active',
    ];

    protected $casts = [
        'active'             => 'boolean',
        'estimated_duration' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function steps(): HasMany
    {
        return $this->hasMany(RepairTemplateStep::class, 'repair_template_id')
            ->orderBy('sequence');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(RepairTemplatePart::class, 'repair_template_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(RepairTemplateChecklist::class, 'repair_template_id');
    }

    // ── Business methods ──────────────────────────────────────────────────────

    public function applyToRepairOrder(RepairOrder $order): void
    {
        app(RepairTemplateService::class)->applyToRepairOrder($this, $order);
    }

    public function generateChecklist(): array
    {
        return $this->checklists->toArray();
    }

    public function generatePartsList(): array
    {
        return $this->parts->toArray();
    }

    public function estimateDuration(): int
    {
        $stepTotal = $this->steps->sum('estimated_duration');

        return (int) ($this->estimated_duration ?? 0) + (int) $stepTotal;
    }

    public function createRepairOrder(array $attributes = []): RepairOrder
    {
        return app(RepairTemplateService::class)->createRepairOrder($this, $attributes);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeForEquipmentType(Builder $query, string $type): Builder
    {
        return $query->where('equipment_type', $type);
    }

    public function scopeForFaultType(Builder $query, string $type): Builder
    {
        return $query->where('fault_type', $type);
    }
}
