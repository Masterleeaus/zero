<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Money\InvoiceItem;
use App\Models\Money\Payment;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'quote_id',
        'invoice_number',
        'title',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'subtotal',
        'tax',
        'total',
        'paid_amount',
        'balance',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'total'      => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance'     => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recomputeBalance(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->balance = max(0, (float) $this->total - (float) $totalPaid);
        $this->save();
    }

    public function recomputeTotalsFromItems(): void
    {
        $items = $this->items;
        $subtotal = $items->sum(fn (InvoiceItem $item) => (float) ($item->quantity * $item->unit_price));
        $tax = $items->sum(function (InvoiceItem $item) {
            $line = (float) ($item->quantity * $item->unit_price);
            return $line * ((float) $item->tax_rate) / 100;
        });

        $this->update([
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'total'    => $subtotal + $tax,
        ]);
    }
}
