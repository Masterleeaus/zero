<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit within a Floor.
 *
 * Table: premise_units (avoids conflict with any legacy `units` table
 * from the Units module source).
 *
 * Supports multi-tenant buildings and strata structures.
 */
class Unit extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'premise_units';

    protected $fillable = [
        'company_id',
        'created_by',
        'floor_id',
        'name',
        'unit_code',
        'status',
        'area_sqm',
        'notes',
    ];

    protected $casts = [
        'area_sqm' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'unit_id');
    }
}
