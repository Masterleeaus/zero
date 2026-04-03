<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyUnit extends BaseModel
{
    use BelongsToCompany;

    protected $table = 'pm_property_units';

    protected $guarded = ['id'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
