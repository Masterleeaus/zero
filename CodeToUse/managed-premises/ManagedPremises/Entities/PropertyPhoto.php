<?php

namespace Modules\ManagedPremises\Entities;

use App\Models\BaseModel;
use Modules\ManagedPremises\Entities\Concerns\BelongsToCompany;

class PropertyPhoto extends BaseModel
{
    use BelongsToCompany;
    public const FILE_PATH = 'property-management/photos';

    protected $table = 'pm_property_photos';
    protected $fillable = ['company_id','user_id','property_id','property_job_id','path','caption'];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
