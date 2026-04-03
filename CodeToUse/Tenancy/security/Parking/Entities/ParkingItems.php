<?php
namespace Modules\Parking\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;

class ParkingItems extends BaseModel
{
    use HasCompany;

    protected $table = 'tenan_parkir_items';
    protected $guarded = ['id'];

}

