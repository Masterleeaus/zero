<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyTag extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_tags';
    protected $fillable = ['company_id','user_id','property_id','tag'];
}
