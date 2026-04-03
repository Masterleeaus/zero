<?php

namespace Modules\PropertyManagement\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PropertyManagement\Entities\Concerns\BelongsToCompany;

class PropertyContact extends BaseModel
{
    use BelongsToCompany;

    protected $table = 'pm_property_contacts';

    protected $guarded = ['id'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
