<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyHazard extends Model
{
    protected $table = 'pm_property_hazards';
    protected $fillable = ['company_id','property_id','hazard','risk_level','controls'];
}
