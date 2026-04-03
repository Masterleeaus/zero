<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;

use Modules\Inventory\Observers\TenantObserver;\nuse Modules\Inventory\Observers\AuditTrail;

class Stocktake extends Model
{\n    use SoftDeletes;\n
    use TenantObserver, AuditTrail;

    protected $table = 'stocktakes';
    protected $guarded = [];
    public function lines(){ return $this->hasMany(StocktakeLine::class); }
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
}
