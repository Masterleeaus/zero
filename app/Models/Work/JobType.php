<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Team\SkillRequirement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobType extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
    ];

    public function serviceJobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class, 'job_type_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(JobTemplate::class, 'job_type_id');
    }

    public function scopeForSelect(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function skillRequirements(): HasMany
    {
        return $this->hasMany(SkillRequirement::class);
    }
}
