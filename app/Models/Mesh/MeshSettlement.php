<?php

declare(strict_types=1);

namespace App\Models\Mesh;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeshSettlement extends Model
{
    use HasFactory;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_PAID     = 'paid';
    public const STATUS_DISPUTED = 'disputed';

    protected $fillable = [
        'mesh_dispatch_request_id',
        'requesting_company_id',
        'fulfilling_company_id',
        'amount',
        'commission_amount',
        'net_amount',
        'currency',
        'status',
        'invoice_reference',
        'settled_at',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount'        => 'decimal:2',
        'settled_at'        => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function dispatchRequest(): BelongsTo
    {
        return $this->belongsTo(MeshDispatchRequest::class, 'mesh_dispatch_request_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where(function (Builder $q) use ($companyId) {
            $q->where('requesting_company_id', $companyId)
              ->orWhere('fulfilling_company_id', $companyId);
        });
    }
}
