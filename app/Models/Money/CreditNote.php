<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_id',
        'invoice_id',
        'reference',
        'title',
        'status',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'issued_at',
        'notes',
    ];

    protected $attributes = [
        'status'   => 'draft',
        'currency' => 'AUD',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total'     => 'decimal:2',
        'issued_at' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }
}
