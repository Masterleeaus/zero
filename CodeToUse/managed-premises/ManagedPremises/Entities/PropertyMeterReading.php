<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyMeterReading extends BaseModel
{
    use BelongsToCompany;

    protected $table = 'pm_meter_readings';

    protected $guarded = ['id'];

    protected $casts = [
        'reading_date' => 'date',
        'current_reading' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'consumed' => 'decimal:2',
        'rate' => 'decimal:4',
        'amount' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class, 'unit_id');
    }
}
