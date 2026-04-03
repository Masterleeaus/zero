<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'description',
        'qty_ordered',
        'qty_received',
        'unit_price',
        'tax_rate',
        'line_total',
    ];

    protected $casts = [
        'qty_ordered'  => 'integer',
        'qty_received' => 'integer',
        'unit_price'   => 'decimal:4',
        'tax_rate'     => 'decimal:2',
        'line_total'   => 'decimal:2',
    ];

    protected $attributes = [
        'qty_received' => 0,
        'tax_rate'     => 0,
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
