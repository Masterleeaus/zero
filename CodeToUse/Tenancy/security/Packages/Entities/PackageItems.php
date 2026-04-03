<?php

namespace Modules\TrPackage\Entities;

use App\Models\BaseModel;
use App\Traits\HasCompany;
use Modules\TrPackage\Entities\Package;
use Modules\Units\Entities\Unit;
use Modules\TrPackage\Entities\Ekspedisi;
use Modules\TrPackage\Entities\TypePackage;

class PackageItems extends BaseModel
{
    use HasCompany;

    protected $table = 'tr_package_items';
    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function ekspedisi()
    {
        return $this->belongsTo(Ekspedisi::class, 'ekspedisi_id');
    }

    public function type()
    {
        return $this->belongsTo(TypePackage::class, 'type_id');
    }

    public function paket()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
