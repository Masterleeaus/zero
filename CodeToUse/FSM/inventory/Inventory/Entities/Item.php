<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;

use Modules\Inventory\Observers\TenantObserver;\nuse Modules\Inventory\Observers\AuditTrail;

class Item extends Model
{\n    use SoftDeletes;\n
    use TenantObserver, AuditTrail;

    protected $table = 'inventory_items';
    protected $fillable = ['name','sku','qty','category','unit_price','tenant_id'];
}
