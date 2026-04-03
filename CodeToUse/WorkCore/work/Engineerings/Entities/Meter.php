<?php
namespace Modules\Engineerings\Entities;

use App\Models\BaseModel;
use App\Traits\IconTrait;
use Modules\Units\Entities\Unit;

class Meter extends BaseModel
{
    use IconTrait;
    protected $table = 'meters';
    protected $guarded = ['id'];

    const FILE_PATH = 'meter-files';

    protected $appends = ['image_url'];

    public function getFileUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('meter-files/' . $this->hashname);
    }

    public function getImageUrlAttribute()
    {
        return (!is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('meter-files/' . $this->image);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

}
