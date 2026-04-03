<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'gl_type',
        'type',
        'parent_id',
        'balance',
        'account_number',
        'bank_name',
        'is_active',
    ];

    protected $casts = [
        'balance'   => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // GL type constants
    public const GL_ASSET     = 'asset';
    public const GL_LIABILITY = 'liability';
    public const GL_EQUITY    = 'equity';
    public const GL_REVENUE   = 'revenue';
    public const GL_EXPENSE   = 'expense';

    public static array $glTypes = [
        self::GL_ASSET,
        self::GL_LIABILITY,
        self::GL_EQUITY,
        self::GL_REVENUE,
        self::GL_EXPENSE,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function ledgerTransactions(): HasMany
    {
        return $this->hasMany(LedgerTransaction::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Whether this account is a normal-debit-balance account (assets + expenses).
     */
    public function isDebitNormal(): bool
    {
        return in_array($this->gl_type, [self::GL_ASSET, self::GL_EXPENSE], true);
    }
}
