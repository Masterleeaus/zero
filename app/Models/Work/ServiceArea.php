<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceArea extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'service_areas';

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'code',
        'description',
        'type',
        'zip_codes',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ServiceAreaBranch::class, 'branch_id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class, 'service_area_id');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getRegionNameAttribute(): ?string
    {
        return $this->branch?->district?->region?->name;
    }

    public function getDistrictNameAttribute(): ?string
    {
        return $this->branch?->district?->name;
    }

    public function getBranchNameAttribute(): ?string
    {
        return $this->branch?->name;
    }
}
