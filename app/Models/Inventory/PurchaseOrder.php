<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'po_number',
        'supplier_id',
        'status',
        'order_date',
        'expected_date',
        'reference',
        'notes',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency_code',
        'received_by',
        'received_at',
        'receiving_notes',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'received_at'   => 'datetime',
        'subtotal'      => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    protected $attributes = [
        'status'        => 'draft',
        'subtotal'      => 0,
        'tax_amount'    => 0,
        'total_amount'  => 0,
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function supplierBills(): HasMany
    {
        return $this->hasMany(\App\Models\Money\SupplierBill::class, 'purchase_order_id');
    }
}
