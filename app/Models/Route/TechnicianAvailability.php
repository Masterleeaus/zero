<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Team\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TechnicianAvailability — working schedule definition for a technician.
 *
 * Merged from fieldservice_route_availability:
 *   - working days bitmask
 *   - default work window (start/end time)
 *   - max work hours / overtime
 *
 * One technician may have multiple TechnicianAvailability records (e.g.
 * different schedules for different periods) but only one should be active
 * at a time.  AvailabilityWindow rows provide per-day overrides/exceptions.
 */
class TechnicianAvailability extends Model
{
    use BelongsToCompany;

    protected $table = 'technician_availabilities';

    protected $fillable = [
        'company_id',
        'user_id',
        'team_id',
        'name',
        'active_days_mask',
        'work_start_time',
        'work_end_time',
        'max_work_hours',
        'max_overtime_hours',
        'is_active',
        'effective_from',
        'effective_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'active_days_mask'   => 'integer',
        'max_work_hours'     => 'float',
        'max_overtime_hours' => 'float',
        'is_active'          => 'boolean',
        'effective_from'     => 'date',
        'effective_to'       => 'date',
    ];

    protected $attributes = [
        'active_days_mask'   => 0b0011111, // Mon–Fri
        'work_start_time'    => '08:00:00',
        'work_end_time'      => '17:00:00',
        'max_work_hours'     => 8.0,
        'max_overtime_hours' => 2.0,
        'is_active'          => true,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function windows(): HasMany
    {
        return $this->hasMany(AvailabilityWindow::class, 'technician_availability_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Whether this schedule is active on the given date (day of week + effective range).
     */
    public function isActiveOn(Carbon $date): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }
        if ($this->effective_to && $date->gt($this->effective_to)) {
            return false;
        }
        $dayIndex = $date->dayOfWeekIso - 1; // 0=Mon … 6=Sun
        return (bool) ($this->active_days_mask & (1 << $dayIndex));
    }

    /**
     * Return the Carbon work window for a specific date based on this schedule.
     * Returns null if the technician is not scheduled to work that day.
     *
     * @return array{start: Carbon, end: Carbon}|null
     */
    public function workWindowFor(Carbon $date): ?array
    {
        if (! $this->isActiveOn($date)) {
            return null;
        }
        return [
            'start' => $date->copy()->setTimeFromTimeString($this->work_start_time),
            'end'   => $date->copy()->setTimeFromTimeString($this->work_end_time),
        ];
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEffectiveOn(Builder $query, Carbon $date): Builder
    {
        $dateStr = $date->toDateString();
        return $query
            ->where('is_active', true)
            ->where(static function (Builder $q) use ($dateStr) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $dateStr);
            })
            ->where(static function (Builder $q) use ($dateStr) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr);
            });
    }
}
