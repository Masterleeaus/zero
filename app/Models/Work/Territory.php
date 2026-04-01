<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'description',
        'type',
        'zip_codes',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForSelect(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function getDistrictAttribute(): ?District
    {
        return $this->branch?->district;
    }

    public function getRegionAttribute(): ?Region
    {
        return $this->branch?->district?->region;
    }

    public function getZipCodeListAttribute(): array
    {
        if (empty($this->zip_codes)) {
            return [];
        }

        return array_map('trim', explode(',', $this->zip_codes));
    }

    public function coversZip(string $zip): bool
    {
        return in_array($zip, $this->getZipCodeListAttribute(), true);
    }
}
