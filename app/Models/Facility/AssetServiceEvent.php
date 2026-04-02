<?php

declare(strict_types=1);

namespace App\Models\Facility;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Work\ServiceJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Service event log for a SiteAsset.
 *
 * Tracks the history of inspections, maintenance, repairs, and replacements
 * performed on a site-installed asset.
 *
 * Event types: inspection | maintenance | repair | replacement | installation | decommission
 * Status:      scheduled | in_progress | completed | failed
 */
class AssetServiceEvent extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'asset_service_events';

    protected $fillable = [
        'company_id',
        'created_by',
        'site_asset_id',
        'service_job_id',
        'event_type',
        'event_date',
        'status',
        'description',
        'findings',
        'actions_taken',
        'cost',
        'performed_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'cost'       => 'decimal:2',
    ];

    protected $attributes = [
        'event_type' => 'maintenance',
        'status'     => 'completed',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function siteAsset(): BelongsTo
    {
        return $this->belongsTo(SiteAsset::class, 'site_asset_id');
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeInspections(Builder $query): Builder
    {
        return $query->where('event_type', 'inspection');
    }

    public function scopeMaintenance(Builder $query): Builder
    {
        return $query->where('event_type', 'maintenance');
    }

    public function scopeRepairs(Builder $query): Builder
    {
        return $query->where('event_type', 'repair');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
