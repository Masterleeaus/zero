<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyKey extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_keys';
    protected $fillable = ['company_id','user_id','property_id','type','location','code','notes'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
