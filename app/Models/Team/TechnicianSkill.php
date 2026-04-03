<?php

declare(strict_types=1);

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianSkill extends Model
{
    use HasFactory;

    /** Ordered levels — lower index = lower rank. */
    public const LEVELS = ['trainee', 'competent', 'proficient', 'expert'];

    protected $fillable = [
        'user_id',
        'skill_definition_id',
        'level',
        'acquired_at',
        'expires_at',
        'endorsed_by',
        'is_verified',
    ];

    protected $casts = [
        'acquired_at' => 'date',
        'expires_at'  => 'date',
        'is_verified' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skillDefinition(): BelongsTo
    {
        return $this->belongsTo(SkillDefinition::class);
    }

    public function endorsedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'endorsed_by');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function meetsLevel(string $minLevel): bool
    {
        $rank    = array_search($this->level, self::LEVELS, true);
        $minRank = array_search($minLevel, self::LEVELS, true);

        return $rank !== false && $minRank !== false && $rank >= $minRank;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(static function (Builder $q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString());
        });
    }
}
