<?php

declare(strict_types=1);

namespace App\Models\Premises;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Structured hazard record for a Premises or Unit.
 *
 * Replaces / extends the free-text `hazards` field on Premises with a
 * structured record supporting severity, instructions, PPE, and
 * restricted-access flags.
 *
 * Severity values: low | medium | high | critical
 * Status values:   active | resolved | monitoring
 */
class Hazard extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'hazards';

    protected $fillable = [
        'company_id',
        'created_by',
        'premises_id',
        'unit_id',
        'service_job_id',
        'title',
        'description',
        'severity',
        'instructions',
        'ppe_required',
        'restricted_access',
        'status',
        'identified_at',
        'resolved_at',
    ];

    protected $casts = [
        'restricted_access' => 'boolean',
        'identified_at'     => 'date',
        'resolved_at'       => 'date',
    ];

    protected $attributes = [
        'severity'          => 'medium',
        'status'            => 'active',
        'restricted_access' => false,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function premises(): BelongsTo
    {
        return $this->belongsTo(Premises::class, 'premises_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function requiresPpe(): bool
    {
        return ! empty($this->ppe_required);
    }
}
