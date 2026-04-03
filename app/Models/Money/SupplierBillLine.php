<?php

declare(strict_types=1);

namespace App\Models\Money;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line item on a supplier bill.
 *
 * Lines are linked to an expense/asset account and optionally
 * to a service job for job-cost tracking.
 */
class SupplierBillLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_bill_id',
        'account_id',
        'service_job_id',
        'description',
        'amount',
        'tax_rate',
        'tax_amount',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'tax_rate'   => 'decimal:4',
        'tax_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'amount'     => 0,
        'tax_rate'   => 0,
        'tax_amount' => 0,
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function supplierBill(): BelongsTo
    {
        return $this->belongsTo(SupplierBill::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
