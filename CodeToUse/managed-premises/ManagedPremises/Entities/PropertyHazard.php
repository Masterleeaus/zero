<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyHazard extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_hazards';
    protected $fillable = ['company_id','user_id','property_id','hazard','risk_level','controls'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
