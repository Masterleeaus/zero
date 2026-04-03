<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Supplier Bill (Accounts Payable document).
 *
 * Represents a vendor invoice received from a supplier.
 * Posting creates: Dr Expense / Cr Accounts Payable
 *
 * Statuses: draft | awaiting_payment | partial | paid | overdue | void
 */
class SupplierBill extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    public const STATUS_DRAFT             = 'draft';
    public const STATUS_AWAITING_PAYMENT  = 'awaiting_payment';
    public const STATUS_PARTIAL           = 'partial';
    public const STATUS_PAID              = 'paid';
    public const STATUS_OVERDUE           = 'overdue';
    public const STATUS_VOID              = 'void';

    protected $fillable = [
        'company_id',
        'created_by',
        'supplier_id',
        'purchase_order_id',
        'reference',
        'bill_date',
        'due_date',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'amount_paid',
        'status',
        'notes',
    ];

    protected $casts = [
        'bill_date'   => 'date',
        'due_date'    => 'date',
        'subtotal'    => 'decimal:2',
        'tax_total'   => 'decimal:2',
        'total'       => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $attributes = [
        'status'      => self::STATUS_DRAFT,
        'currency'    => 'AUD',
        'subtotal'    => 0,
        'tax_total'   => 0,
        'total'       => 0,
        'amount_paid' => 0,
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SupplierBillLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function getBalanceAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function isFullyPaid(): bool
    {
        return (float) $this->amount_paid >= (float) $this->total;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! $this->isFullyPaid()
            && $this->status !== self::STATUS_VOID;
    }
}
