<?php

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DispatchConstraint extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'constraint_type',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'weight'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
