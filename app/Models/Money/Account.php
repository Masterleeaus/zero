<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Chart-of-Accounts entry.
 *
 * Types: asset | liability | equity | income | expense
 */
class Account extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    /**
     * Allowed account types.
     */
    public const TYPES = ['asset', 'liability', 'equity', 'income', 'expense'];

    protected $fillable = [
        'company_id',
        'created_by',
        'code',
        'name',
        'type',
        'description',
        'is_active',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /** Journal lines that debit this account. */
    public function debitLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'debit_account_id');
    }

    /** Journal lines that credit this account. */
    public function creditLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'credit_account_id');
    }

    /** Child accounts (sub-accounts). */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Parent account. */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Compute the running balance for this account (debits - credits). */
    public function runningBalance(): float
    {
        $debits  = (float) $this->debitLines()->sum('amount');
        $credits = (float) $this->creditLines()->sum('amount');

        return match ($this->type) {
            'asset', 'expense' => $debits - $credits,
            default            => $credits - $debits,
        };
    }

    /** Scopes ---------------------------------------------------------------- */

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(\Illuminate\Database\Eloquent\Builder $query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', $type);
    }
}
