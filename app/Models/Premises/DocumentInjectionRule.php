<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Defines a rule that automatically injects a document into jobs/inspections.
 *
 * rule_type values: job_type | asset_type | service_type | access_level | premises_zone
 */
class DocumentInjectionRule extends Model
{
    use BelongsToCompany;

    protected $table = 'document_injection_rules';

    protected $fillable = [
        'company_id',
        'rule_type',
        'rule_value',
        'document_id',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active'    => 'boolean',
    ];

    protected $attributes = [
        'is_mandatory' => false,
        'is_active'    => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(FacilityDocument::class, 'document_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $ruleType): Builder
    {
        return $query->where('rule_type', $ruleType);
    }

    public function scopeForValue(Builder $query, string $ruleValue): Builder
    {
        return $query->where('rule_value', $ruleValue);
    }
}
