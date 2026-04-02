<?php

declare(strict_types=1);

namespace App\Models\Meter;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timestamped meter reading.
 *
 * Source values:  manual | auto | import | estimate
 * Anomaly flag is set when the reading exceeds the meter's threshold_high
 * or falls below threshold_low.
 */
class MeterReading extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'meter_readings';

    protected $fillable = [
        'company_id',
        'created_by',
        'meter_id',
        'reading',
        'consumed',
        'rate',
        'amount',
        'reading_date',
        'read_at',
        'source',
        'anomaly_flagged',
        'anomaly_reason',
        'notes',
        'reader_id',
    ];

    protected $casts = [
        'reading'         => 'decimal:3',
        'consumed'        => 'decimal:3',
        'rate'            => 'decimal:4',
        'amount'          => 'decimal:2',
        'reading_date'    => 'date',
        'read_at'         => 'datetime',
        'anomaly_flagged' => 'boolean',
    ];

    protected $attributes = [
        'source'          => 'manual',
        'anomaly_flagged' => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class, 'meter_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAnomalies(Builder $query): Builder
    {
        return $query->where('anomaly_flagged', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasAnomaly(): bool
    {
        return (bool) $this->anomaly_flagged;
    }
}
