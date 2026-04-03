<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\Work\JobType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_type_id',
        'skill_definition_id',
        'minimum_level',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function skillDefinition(): BelongsTo
    {
        return $this->belongsTo(SkillDefinition::class);
    }
}
