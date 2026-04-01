<?php

declare(strict_types=1);

namespace App\Models\Equipment;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\Work\ServiceAgreement;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FSM Module 7 — Equipment
 *
 * Serialised equipment catalogue.  Supports product → serial-number lifecycle,
 * site/customer/job linkages, and replacement tracking.
 *
 * Status values: in_stock | installed | removed | retired | lost
 */
class Equipment extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;
    use SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'model',
        'manufacturer',
        'serial_number',
        'category',
        'status',
        'customer_id',
        'site_id',
        'premises_id',
        'service_job_id',
        'agreement_id',
        'invoice_id',
        'install_date',
        'warranty_expiry',
        'notes',
    ];

    protected $casts = [
        'install_date'    => 'date',
        'warranty_expiry' => 'date',
    ];

    protected $attributes = [
        'status' => 'in_stock',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function premises(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Premises\Premises::class);
    }

    public function serviceJob(): BelongsTo
    {
        return $this->belongsTo(ServiceJob::class, 'service_job_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(ServiceAgreement::class, 'agreement_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installations(): HasMany
    {
        return $this->hasMany(InstalledEquipment::class, 'equipment_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(EquipmentMovement::class, 'equipment_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Current active installation record, if any. */
    public function currentInstallation(): ?InstalledEquipment
    {
        return $this->installations()
            ->where('status', 'active')
            ->latest('installed_at')
            ->first();
    }

    public function isInstalled(): bool
    {
        return $this->status === 'installed';
    }

    public function isUnderWarranty(): bool
    {
        if (! $this->warranty_expiry) {
            return false;
        }

        return $this->warranty_expiry->isFuture();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeInstalled(Builder $query): Builder
    {
        return $query->where('status', 'installed');
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('status', 'in_stock');
    }

    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForPremises(Builder $query, int $premisesId): Builder
    {
        return $query->where('premises_id', $premisesId);
    }
}
