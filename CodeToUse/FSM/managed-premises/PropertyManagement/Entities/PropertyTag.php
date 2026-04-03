<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyTag extends Model
{
    protected $table = 'pm_property_tags';
    protected $fillable = ['company_id','property_id','tag'];
}
