<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;

use Modules\Inventory\Observers\TenantObserver;\nuse Modules\Inventory\Observers\AuditTrail;

class StockMovement extends Model
{\n    use SoftDeletes;\n
    use TenantObserver, AuditTrail;

    protected $table = 'stock_movements';
    protected $guarded = [];

    public function item(){ return $this->belongsTo(Item::class,'item_id'); }
    public function warehouse(){ return $this->belongsTo(Warehouse::class,'warehouse_id'); }
}

