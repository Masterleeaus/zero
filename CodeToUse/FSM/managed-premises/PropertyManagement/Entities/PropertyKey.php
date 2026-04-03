<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyKey extends Model
{
    protected $table = 'pm_property_keys';
    protected $fillable = ['company_id','property_id','type','location','code','notes'];
}
