<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyChecklist extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_checklists';
    protected $fillable = ['company_id','user_id','property_id','type','title','items_json'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
