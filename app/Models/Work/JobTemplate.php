<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobTemplate extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'job_type_id',
        'team_id',
        'name',
        'instructions',
        'duration',
    ];

    protected $casts = [
        'duration' => 'float',
    ];

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class, 'job_type_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeForSelect(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
