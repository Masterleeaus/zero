<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Double-entry journal entry header.
 *
 * Statuses: draft | posted | void
 */
class JournalEntry extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_VOID   = 'void';

    protected $fillable = [
        'company_id',
        'created_by',
        'reference',
        'description',
        'entry_date',
        'status',
        'source_type',
        'source_id',
        'currency',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    protected $attributes = [
        'status'   => self::STATUS_DRAFT,
        'currency' => 'AUD',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /** Polymorphic link back to the originating document (Invoice, Payment, Expense …). */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Total debit side of this entry. */
    public function totalDebits(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    /** Total credit side of this entry. */
    public function totalCredits(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    /** Is the entry balanced (debits === credits)? */
    public function isBalanced(): bool
    {
        return abs($this->totalDebits() - $this->totalCredits()) < 0.001;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
