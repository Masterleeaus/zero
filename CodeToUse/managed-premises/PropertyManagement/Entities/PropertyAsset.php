<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyAsset extends Model
{
    protected $table = 'pm_property_assets';
    protected $fillable = ['company_id','property_id','label','category','serial','location','notes'];
}
