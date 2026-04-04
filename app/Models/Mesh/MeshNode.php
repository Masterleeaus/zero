<?php

declare(strict_types=1);

namespace App\Models\Mesh;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeshNode extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use SoftDeletes;

    public const TRUST_OBSERVER  = 'observer';
    public const TRUST_STANDARD  = 'standard';
    public const TRUST_TRUSTED   = 'trusted';
    public const TRUST_PARTNER   = 'partner';

    /** Ordered levels — lower index = lower trust rank. */
    public const TRUST_LEVELS = [
        self::TRUST_OBSERVER,
        self::TRUST_STANDARD,
        self::TRUST_TRUSTED,
        self::TRUST_PARTNER,
    ];

    protected $fillable = [
        'company_id',
        'node_id',
        'node_name',
        'node_url',
        'trust_level',
        'public_key',
        'is_active',
        'last_handshake_at',
        'capabilities_hash',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'last_handshake_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function trustEvents(): HasMany
    {
        return $this->hasMany(MeshTrustEvent::class, 'node_id', 'node_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithMinTrustLevel(Builder $query, string $minLevel): Builder
    {
        $idx = array_search($minLevel, self::TRUST_LEVELS, true);

        if ($idx === false) {
            return $query;
        }

        return $query->whereIn('trust_level', array_slice(self::TRUST_LEVELS, $idx));
    }

    public function scopeObserver(Builder $query): Builder
    {
        return $query->where('trust_level', self::TRUST_OBSERVER);
    }

    public function scopeStandard(Builder $query): Builder
    {
        return $query->where('trust_level', self::TRUST_STANDARD);
    }

    public function scopeTrusted(Builder $query): Builder
    {
        return $query->where('trust_level', self::TRUST_TRUSTED);
    }

    public function scopePartner(Builder $query): Builder
    {
        return $query->where('trust_level', self::TRUST_PARTNER);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function meetsMinTrustLevel(string $minLevel): bool
    {
        $nodeIdx = array_search($this->trust_level, self::TRUST_LEVELS, true);
        $minIdx  = array_search($minLevel, self::TRUST_LEVELS, true);

        return $nodeIdx !== false && $minIdx !== false && $nodeIdx >= $minIdx;
    }
}
