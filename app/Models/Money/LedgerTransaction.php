<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ledger transaction — single-entry posting against an Account balance.
 * Renamed from source `Transaction` to `LedgerTransaction` to avoid
 * collision with any future platform-level payment transaction reference.
 */
class LedgerTransaction extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'account_id',
        'journal_entry_id',
        'type',
        'amount',
        'category',
        'reference',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount'           => 'decimal:2',
    ];

    // Type constants
    public const TYPE_INCOME       = 'income';
    public const TYPE_EXPENSE      = 'expense';
    public const TYPE_TRANSFER_IN  = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
