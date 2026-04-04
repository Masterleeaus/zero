<?php

declare(strict_types=1);

namespace App\Models\Mesh;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Append-only event log — records must never be mutated after creation.
 */
class MeshTrustEvent extends Model
{
    public const UPDATED_AT = null; // no updated_at column

    protected $fillable = [
        'company_id',
        'node_id',
        'event_type',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'occurred_at' => 'datetime',
    ];

    // ── Immutability guard ───────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::updating(static function () {
            throw new RuntimeException('MeshTrustEvent records are append-only and cannot be updated.');
        });
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForNode(Builder $query, string $nodeId): Builder
    {
        return $query->where('node_id', $nodeId);
    }

    public function scopeOfType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }
}
