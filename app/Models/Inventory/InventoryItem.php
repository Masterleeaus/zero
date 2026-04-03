<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'sku',
        'description',
        'category',
        'unit_price',
        'cost_price',
        'qty_on_hand',
        'reorder_point',
        'unit',
        'track_quantity',
        'status',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:4',
        'cost_price'      => 'decimal:4',
        'qty_on_hand'     => 'integer',
        'reorder_point'   => 'integer',
        'track_quantity'  => 'boolean',
    ];

    protected $attributes = [
        'status'         => 'active',
        'track_quantity' => true,
        'qty_on_hand'    => 0,
        'reorder_point'  => 0,
    ];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'item_id');
    }

    public function stocktakeLines(): HasMany
    {
        return $this->hasMany(StocktakeLine::class, 'item_id');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id');
    }
}
