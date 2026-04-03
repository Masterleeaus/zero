use Modules\Inventory\Observers\AuditTrail;\n<?php

namespace Modules\Inventory\Entities;

use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\SoftDeletes;

class StocktakeLine extends Model
{\n    use AuditTrail;\n\n    use SoftDeletes;\n
    protected $table = 'stocktake_lines';
    protected $guarded = [];
    public function stocktake(){ return $this->belongsTo(Stocktake::class); }
    public function item(){ return $this->belongsTo(Item::class); }
}
