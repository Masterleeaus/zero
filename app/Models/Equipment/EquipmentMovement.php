<?php

declare(strict_types=1);

namespace App\Models\Equipment;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Movement / stock log for equipment.
 *
 * Movement types:
 *   installed | removed | replaced | consumed |
 *   assigned_to_site | assigned_to_job
 *
 * Corresponds to Odoo stock.move concept adapted for FSM.
 * If a stock domain is added later, link movement_id → stock movement tables.
 */
class EquipmentMovement extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'equipment_movements';

    protected $fillable = [
        'company_id',
        'created_by',
        'equipment_id',
        'service_job_id',
        'site_id',
        'premises_id',
        'movement_type',
        'notes',
        'moved_at',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Premises\Premises::class);
    }
}
