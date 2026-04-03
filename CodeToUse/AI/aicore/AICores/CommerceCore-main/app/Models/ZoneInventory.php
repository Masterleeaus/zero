<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoneInventory extends Model
{
    protected $fillable = [
        'warehouse_zone_id',
        'product_id',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'warehouse_zone_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
