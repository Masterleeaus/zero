<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyRoom extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_rooms';
    protected $fillable = ['company_id','user_id','property_id','name','type','notes'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
