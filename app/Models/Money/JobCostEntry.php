<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * JobCostEntry — cost captured against a service job.
 *
 * Bridges the Work/ServiceJob domain to the Finance ledger.
 * Cost types: labour | material | equipment | subcontractor | overhead
 */
class JobCostEntry extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    public const TYPE_LABOUR        = 'labour';
    public const TYPE_MATERIAL      = 'material';
    public const TYPE_EQUIPMENT     = 'equipment';
    public const TYPE_SUBCONTRACTOR = 'subcontractor';
    public const TYPE_OVERHEAD      = 'overhead';

    public const TYPES = [
        self::TYPE_LABOUR,
        self::TYPE_MATERIAL,
        self::TYPE_EQUIPMENT,
        self::TYPE_SUBCONTRACTOR,
        self::TYPE_OVERHEAD,
    ];

    protected $fillable = [
        'company_id',
        'created_by',
        'service_job_id',
        'cost_type',
        'description',
        'quantity',
        'unit_cost',
        'total_cost',
        'cost_date',
        'reference',
        'account_id',
        'journal_entry_id',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'cost_date'  => 'date',
        'quantity'   => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity'  => 1,
        'unit_cost' => 0,
        'total_cost' => 0,
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /** Polymorphic source (Expense, SupplierBill, TimesheetSubmission …). */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // -----------------------------------------------------------------------
    // Hooks
    // -----------------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            $entry->total_cost = round((float) $entry->quantity * (float) $entry->unit_cost, 2);
        });
    }
}
