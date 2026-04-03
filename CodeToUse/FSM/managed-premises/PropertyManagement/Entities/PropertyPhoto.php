<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyPhoto extends Model
{
    public const FILE_PATH = 'property-management/photos';

    protected $table = 'pm_property_photos';
    protected $fillable = ['company_id','property_id','path','caption'];
}
