<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;

use Modules\Inventory\Observers\TenantObserver;\nuse Modules\Inventory\Observers\AuditTrail;

class Warehouse extends Model
{\n    use SoftDeletes;\n
    use TenantObserver, AuditTrail;

    protected $table = 'warehouses';
    protected $guarded = [];
}
