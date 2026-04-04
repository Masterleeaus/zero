<?php

declare(strict_types=1);

namespace App\Models\Mesh;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeshDispatchRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_OPEN       = 'open';
    public const STATUS_OFFERED    = 'offered';
    public const STATUS_ACCEPTED   = 'accepted';
    public const STATUS_EXECUTING  = 'executing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_REJECTED   = 'rejected';
    public const STATUS_EXPIRED    = 'expired';

    protected $fillable = [
        'requesting_company_id',
        'fulfilling_company_id',
        'original_job_id',
        'required_capabilities',
        'location',
        'urgency',
        'status',
        'offered_at',
        'accepted_at',
        'completed_at',
        'mesh_job_reference',
        'evidence_hash',
        'commission_rate',
    ];

    protected $casts = [
        'required_capabilities' => 'array',
        'location'              => 'array',
        'offered_at'            => 'datetime',
        'accepted_at'           => 'datetime',
        'completed_at'          => 'datetime',
        'commission_rate'       => 'decimal:4',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function settlement(): HasOne
    {
        return $this->hasOne(MeshSettlement::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_OPEN,
            self::STATUS_OFFERED,
            self::STATUS_ACCEPTED,
            self::STATUS_EXECUTING,
        ]);
    }

    public function scopeForRequestingCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('requesting_company_id', $companyId);
    }

    public function scopeForFulfillingCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('fulfilling_company_id', $companyId);
    }
}
