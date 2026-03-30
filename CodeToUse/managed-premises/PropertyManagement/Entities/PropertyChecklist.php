<?php

namespace Modules\PropertyManagement\Entities;

use Illuminate\Database\Eloquent\Model;

class PropertyChecklist extends Model
{
    protected $table = 'pm_property_checklists';
    protected $fillable = ['company_id','property_id','type','title','items_json'];
}
