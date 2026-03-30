<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyRoom extends Model
{
    protected $table = 'pm_property_rooms';
    protected $fillable = ['company_id','property_id','name','type','notes'];
}
