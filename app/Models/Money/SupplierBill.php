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
 * Supplier Bill — Accounts Payable document.
 *
 * Bridges the Inventory/Supplier + PurchaseOrder domain to the Money ledger.
 * Statuses: draft | approved | paid | cancelled
 *
 * Journal auto-posting (Phase 7):
 *   On approval: Dr Expense/Asset / Cr Accounts Payable
 *   On payment:  Dr Accounts Payable / Cr Bank Account
 */
class SupplierBill extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_PAID      = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'created_by',
        'supplier_id',
        'purchase_order_id',
        'bill_number',
        'reference',
        'bill_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'currency',
        'notes',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'bill_date'   => 'date',
        'due_date'    => 'date',
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
        'subtotal'    => 'decimal:2',
        'tax_amount'  => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $attributes = [
        'status'       => self::STATUS_DRAFT,
        'subtotal'     => 0,
        'tax_amount'   => 0,
        'total_amount' => 0,
        'amount_paid'  => 0,
        'currency'     => 'AUD',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierBillItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function balanceDue(): float
    {
        return (float) $this->total_amount - (float) $this->amount_paid;
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function recalculate(): void
    {
        $this->subtotal     = (float) $this->items()->sum('amount');
        $this->tax_amount   = (float) $this->items()->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->save();
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeUnpaid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_APPROVED]);
    }

    public function scopeOverdue(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', '!=', self::STATUS_PAID)
                     ->where('due_date', '<', now()->toDateString());
    }
}
