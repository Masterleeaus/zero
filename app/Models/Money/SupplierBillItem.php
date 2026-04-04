<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Individual line item on a SupplierBill.
 */
class SupplierBillItem extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'supplier_bill_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'tax_amount',
        'account_id',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity'   => 1,
        'unit_price' => 0,
        'amount'     => 0,
        'tax_amount' => 0,
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function bill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class, 'supplier_bill_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // -----------------------------------------------------------------------
    // Hooks
    // -----------------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $item->amount = round((float) $item->quantity * (float) $item->unit_price, 2);
        });
    }
}
