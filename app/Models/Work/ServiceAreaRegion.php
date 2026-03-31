<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceAreaRegion extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'service_area_regions';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'manager_user_id',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function districts(): HasMany
    {
        return $this->hasMany(ServiceAreaDistrict::class, 'region_id');
    }
}
