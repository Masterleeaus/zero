<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyServiceWindow extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_service_windows';
    protected $fillable = ['company_id','user_id','property_id','days','time_from','time_to','notes'];
}
