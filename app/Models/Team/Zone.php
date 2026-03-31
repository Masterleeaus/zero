<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
        'color'     => '#6366f1',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
