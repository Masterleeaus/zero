<?php

declare(strict_types=1);

namespace App\Models\Inspection;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\OwnedByUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recurring inspection scheduling configuration.
 *
 * Binds an InspectionTemplate to a scope (premises/building/unit) with a
 * frequency and interval, tracking next_due_at and last_completed_at.
 *
 * Frequency values: daily | weekly | monthly | quarterly | annual
 */
class InspectionSchedule extends Model
{
    use HasFactory;
    use BelongsToCompany;
    use OwnedByUser;

    protected $table = 'inspection_schedules';

    protected $fillable = [
        'company_id',
        'created_by',
        'inspection_template_id',
        'scope_type',
        'scope_id',
        'name',
        'frequency',
        'interval',
        'starts_on',
        'ends_on',
        'next_due_at',
        'last_completed_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'starts_on'          => 'date',
        'ends_on'            => 'date',
        'next_due_at'        => 'date',
        'last_completed_at'  => 'date',
        'is_active'          => 'boolean',
        'interval'           => 'integer',
    ];

    protected $attributes = [
        'frequency' => 'monthly',
        'interval'  => 1,
        'is_active' => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(InspectionInstance::class, 'inspection_schedule_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('next_due_at', '<=', now()->toDateString());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Advance next_due_at by the configured frequency * interval.
     */
    public function advanceNextDue(): void
    {
        if (! $this->next_due_at) {
            return;
        }

        $this->next_due_at = $this->nextDateAfter($this->next_due_at);
        $this->last_completed_at = now()->toDateString();
        $this->save();
    }

    protected function nextDateAfter(\Carbon\Carbon|\DateTimeInterface $from): string
    {
        $date = \Carbon\Carbon::parse($from);

        return match ($this->frequency) {
            'daily'     => $date->addDays($this->interval)->toDateString(),
            'weekly'    => $date->addWeeks($this->interval)->toDateString(),
            'monthly'   => $date->addMonthsNoOverflow($this->interval)->toDateString(),
            'quarterly' => $date->addMonthsNoOverflow(3 * $this->interval)->toDateString(),
            'annual'    => $date->addYears($this->interval)->toDateString(),
            default     => $date->addMonthsNoOverflow($this->interval)->toDateString(),
        };
    }
}
