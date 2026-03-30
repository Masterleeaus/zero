<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyAsset extends BaseModel
{
    use BelongsToCompany;
    protected $table = 'pm_property_assets';
    protected $fillable = ['company_id','user_id','property_id','label','category','serial','location','notes'];
}
