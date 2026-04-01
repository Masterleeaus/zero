<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'rate',
        'is_default',
        'is_compound',
        'description',
    ];

    protected $attributes = [
        'is_default'  => false,
        'is_compound' => false,
    ];

    protected $casts = [
        'rate'        => 'decimal:2',
        'is_default'  => 'boolean',
        'is_compound' => 'boolean',
    ];
}
