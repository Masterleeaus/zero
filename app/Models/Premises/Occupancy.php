<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Occupancy record for a Unit.
 *
 * Tracks current and historical tenants/occupants.
 * Supports: tenant history, lease intervals, access rights.
 *
 * Occupant types: customer | contact | user
 * Status values:  active | vacated | pending | terminated
 */
class Occupancy extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'occupancies';

    protected $fillable = [
        'company_id',
        'created_by',
        'unit_id',
        'occupant_type',
        'occupant_id',
        'occupancy_type',
        'start_date',
        'end_date',
        'status',
        'contract_ref',
        'access_rights',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    protected $attributes = [
        'occupant_type' => 'customer',
        'status'        => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(static function (Builder $q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCurrent(): bool
    {
        return $this->isActive()
            && ($this->end_date === null || $this->end_date->gte(now()->startOfDay()));
    }
}
