<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'period_type', 'starts_at', 'ends_at', 'status', 'notes'];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    protected $attributes = [
        'status'      => 'draft',
        'period_type' => 'monthly',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }
}
