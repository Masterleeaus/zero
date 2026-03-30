<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class Property extends BaseModel
{
    use BelongsToCompany;

    protected $table = 'pm_properties';

    protected $guarded = ['id'];

    protected $casts = [
        'preferred_window_start' => 'datetime',
        'preferred_window_end' => 'datetime',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(PropertyUnit::class, 'property_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(PropertyContact::class, 'property_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(PropertyJob::class, 'property_id');
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(PropertyMeterReading::class, 'property_id')->orderByDesc('reading_date');
    }

}
