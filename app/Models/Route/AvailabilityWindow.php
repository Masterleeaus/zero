<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AvailabilityWindow — an individual time block for a technician.
 *
 * Can represent:
 *   - available   : extra/override available window
 *   - unavailable : blocked period (e.g. meeting, break)
 *   - leave       : full-day or partial leave
 *   - travel      : in-transit window
 *   - break       : scheduled break
 *
 * Windows can be one-off (window_date set) or recurring (is_recurring + recurring_days_mask).
 */
class AvailabilityWindow extends Model
{
    use BelongsToCompany;

    protected $table = 'availability_windows';

    protected $fillable = [
        'company_id',
        'user_id',
        'technician_availability_id',
        'window_type',
        'window_date',
        'start_time',
        'end_time',
        'is_recurring',
        'recurring_days_mask',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'window_date'          => 'date',
        'is_recurring'         => 'boolean',
        'recurring_days_mask'  => 'integer',
    ];

    protected $attributes = [
        'window_type'          => 'available',
        'is_recurring'         => false,
        'recurring_days_mask'  => 0,
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technicianAvailability(): BelongsTo
    {
        return $this->belongsTo(TechnicianAvailability::class, 'technician_availability_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Whether this window applies on the given date.
     */
    public function appliesToDate(Carbon $date): bool
    {
        if ($this->is_recurring) {
            $dayIndex = $date->dayOfWeekIso - 1; // 0=Mon … 6=Sun
            return (bool) ($this->recurring_days_mask & (1 << $dayIndex));
        }
        return $this->window_date && $this->window_date->isSameDay($date);
    }

    /**
     * Whether this window blocks availability (unavailable / leave / travel outside work).
     */
    public function isBlockingType(): bool
    {
        return in_array($this->window_type, ['unavailable', 'leave', 'break'], true);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOnDate(Builder $query, Carbon $date): Builder
    {
        $dayIndex = $date->dayOfWeekIso - 1;
        $dayMask  = 1 << $dayIndex;
        $dateStr  = $date->toDateString();

        return $query->where(static function (Builder $q) use ($dateStr, $dayMask) {
            $q->where('window_date', $dateStr)
              ->orWhere(static function (Builder $inner) use ($dayMask) {
                  $inner->where('is_recurring', true)
                        ->whereRaw('(recurring_days_mask & ?) > 0', [$dayMask]);
              });
        });
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('window_type', ['unavailable', 'leave', 'break']);
    }
}
