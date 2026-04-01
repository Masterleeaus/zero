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
 * Tracks a piece of equipment as site-installed.
 *
 * Separate from stock inventory — this record represents a physical installation
 * at a site/premises, with dates and status.
 *
 * Status values: active | removed | replaced
 */
class InstalledEquipment extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'installed_equipment';

    protected $fillable = [
        'company_id',
        'created_by',
        'equipment_id',
        'site_id',
        'premises_id',
        'customer_id',
        'service_job_id',
        'installed_at',
        'removed_at',
        'status',
        'location_description',
        'notes',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'removed_at'   => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Premises\Premises::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Crm\Customer::class);
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }
}
