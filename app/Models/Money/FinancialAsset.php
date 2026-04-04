<?php

declare(strict_types=1);

namespace App\Models\Money;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FinancialAsset — capital asset register entry.
 *
 * Named `FinancialAsset` to avoid collision with `Facility/SiteAsset`.
 * Depreciation method: straight-line (percentage of cost per period).
 *
 * Journal auto-posting (Phase 7 depreciation command):
 *   Dr Depreciation Expense / Cr Accumulated Depreciation
 */
class FinancialAsset extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_DISPOSED = 'disposed';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'category',
        'purchase_order_id',
        'acquisition_date',
        'acquisition_cost',
        'current_value',
        'depreciation_rate',
        'depreciation_method',
        'status',
        'disposal_date',
        'disposal_value',
        'notes',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'disposal_date'    => 'date',
        'acquisition_cost' => 'decimal:2',
        'current_value'    => 'decimal:2',
        'disposal_value'   => 'decimal:2',
        'depreciation_rate' => 'decimal:4',
    ];

    protected $attributes = [
        'status'               => self::STATUS_ACTIVE,
        'acquisition_cost'     => 0,
        'current_value'        => 0,
        'depreciation_rate'    => 0,
        'depreciation_method'  => 'straight_line',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Inventory\PurchaseOrder::class, 'purchase_order_id');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** Compute this period's depreciation charge (monthly). */
    public function monthlyDepreciationCharge(): float
    {
        if ($this->status === self::STATUS_DISPOSED) {
            return 0.0;
        }

        return round((float) $this->acquisition_cost * (float) $this->depreciation_rate / 12, 2);
    }

    public function applyDepreciation(): void
    {
        $charge = $this->monthlyDepreciationCharge();
        $newValue = max(0.0, (float) $this->current_value - $charge);
        $this->current_value = $newValue;
        $this->save();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
