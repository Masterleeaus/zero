<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Work\JobType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillDefinition extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'category',
        'description',
        'requires_certification',
        'expiry_months',
        'is_active',
    ];

    protected $casts = [
        'requires_certification' => 'boolean',
        'is_active'              => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function technicianSkills(): HasMany
    {
        return $this->hasMany(TechnicianSkill::class);
    }

    public function skillRequirements(): HasMany
    {
        return $this->hasMany(SkillRequirement::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRequiresCertification(Builder $query): Builder
    {
        return $query->where('requires_certification', true);
    }
}
