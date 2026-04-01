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
 * Floor within a Building.
 *
 * Table: premise_floors (avoids conflict with any legacy `floors` table
 * from the Units module source).
 */
class Floor extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'premise_floors';

    protected $fillable = [
        'company_id',
        'created_by',
        'building_id',
        'name',
        'floor_code',
        'level_number',
        'notes',
    ];

    protected $casts = [
        'level_number' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'floor_id');
    }
}
