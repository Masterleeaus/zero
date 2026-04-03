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

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Whether this installation is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Service jobs performed at the same premises as this installation.
     *
     * Provides a maintenance history proxy until a dedicated service log is added.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceJob>
     */
    public function maintenanceHistory(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->premises_id) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return ServiceJob::query()
            ->where('premises_id', $this->premises_id)
            ->where('company_id', $this->company_id)
            ->where('status', 'completed')
            ->orderByDesc('date_end')
            ->get();
    }

    /**
     * Inspection instances scoped to this installed equipment's premises.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inspection\InspectionInstance>
     */
    public function inspectionHistory(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->premises_id) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return \App\Models\Inspection\InspectionInstance::query()
            ->where('scope_type', \App\Models\Premises\Premises::class)
            ->where('scope_id', $this->premises_id)
            ->orderByDesc('scheduled_at')
            ->get();
    }
}
