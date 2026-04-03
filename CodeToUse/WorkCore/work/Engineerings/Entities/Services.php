<?php

namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Models\UnitType;
use App\Traits\HasCompany;
use App\Traits\IconTrait;
use App\Traits\CustomFieldsTrait;

class Services extends BaseModel
{
    use CustomFieldsTrait;
    use HasCompany;
    use IconTrait;

    protected $table = 'services';
    protected $guarded = ['id'];

    const FILE_PATH = 'services-files';

    protected $appends = ['image_url'];

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('services-files/' . $this->hashname);
    }

    public function getImageUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('services-files/' . $this->image);
    }

    public function category()
    {
        return $this->belongsTo(ServicesCategory::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(ServicesSubCategory::class, 'sub_category_id');
    }
}