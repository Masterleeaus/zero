<?php

declare(strict_types=1);

namespace App\Models\Vehicle;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VehicleStock — a stock line item carried onboard a vehicle.
 *
 * Maps from Odoo fieldservice_vehicle_stock: stock kept per vehicle.
 *
 * Stock items can be reserved for a specific job, consumed on site,
 * or replenished back to the vehicle from a warehouse.
 */
class VehicleStock extends Model
{
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'vehicle_stock';

    // Status constants
    public const STATUS_AVAILABLE  = 'available';
    public const STATUS_RESERVED   = 'reserved';
    public const STATUS_CONSUMED   = 'consumed';
    public const STATUS_RETURNED   = 'returned';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'item_name',
        'sku',
        'quantity',
        'quantity_reserved',
        'quantity_consumed',
        'unit',
        'reserved_for_job_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity'          => 'float',
        'quantity_reserved' => 'float',
        'quantity_consumed' => 'float',
    ];

    protected $attributes = [
        'status'            => self::STATUS_AVAILABLE,
        'quantity'          => 0,
        'quantity_reserved' => 0,
        'quantity_consumed' => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function reservedForJob(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Work\ServiceJob::class, 'reserved_for_job_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('reserved_for_job_id', $jobId);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Quantity currently available (not reserved or consumed). */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0.0, $this->quantity - $this->quantity_reserved);
    }

    /**
     * Reserve a quantity of this stock item for a job.
     */
    public function reserveForJob(\App\Models\Work\ServiceJob $job, float $qty): void
    {
        $this->quantity_reserved   += $qty;
        $this->reserved_for_job_id  = $job->id;
        $this->status               = self::STATUS_RESERVED;
        $this->save();
    }

    /**
     * Record consumption of stock on a job site.
     */
    public function consume(float $qty): void
    {
        $this->quantity_consumed += $qty;
        $this->quantity          -= $qty;
        $this->quantity_reserved  = max(0.0, $this->quantity_reserved - $qty);
        $this->status             = self::STATUS_CONSUMED;
        $this->save();
    }
}
