<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyServiceWindow extends Model
{
    protected $table = 'pm_property_service_windows';
    protected $fillable = ['company_id','property_id','days','time_from','time_to','notes'];
}
