<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'team_id',
        'name',
        'reference',
        'address',
        'status',
        'start_date',
        'deadline',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline'   => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(ServiceJob::class);
    }
}
