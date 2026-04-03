<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    use HasFactory;
    use BelongsToCompany;

    public const STATUS_ACTIVE  = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'company_id',
        'certification_name',
        'issuing_body',
        'certificate_number',
        'issued_at',
        'expires_at',
        'document_path',
        'status',
    ];

    protected $casts = [
        'issued_at'  => 'date',
        'expires_at' => 'date',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(static function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
            });
    }

    public function scopeExpiringSoon(Builder $query, int $withinDays = 30): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->toDateString(), now()->addDays($withinDays)->toDateString()]);
    }
}
