<?php

declare(strict_types=1);

namespace App\Models\Work;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceAreaDistrict extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'service_area_districts';

    protected $fillable = [
        'company_id',
        'region_id',
        'name',
        'description',
        'manager_user_id',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(ServiceAreaRegion::class, 'region_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ServiceAreaBranch::class, 'district_id');
    }
}
