<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RouteBlackoutDay — a specific blocked date (optionally scoped to postcode).
 *
 * Merged from fieldservice_route_availability:
 *   fsm.blackout.day — date + optional zip
 *
 * When zip is null the blackout applies to all locations on this route.
 * When zip is set it only blocks orders whose premises postcode matches.
 */
class RouteBlackoutDay extends Model
{
    use BelongsToCompany;

    protected $table = 'route_blackout_days';

    protected $fillable = [
        'company_id',
        'name',
        'blackout_date',
        'zip',
        'reason',
        'blackout_group_id',
        'created_by',
    ];

    protected $casts = [
        'blackout_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function blackoutGroup(): BelongsTo
    {
        return $this->belongsTo(RouteBlackoutGroup::class, 'blackout_group_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Whether this blackout applies to the given date and optional postcode.
     */
    public function appliesTo(Carbon $date, ?string $zip = null): bool
    {
        if (! $this->blackout_date->isSameDay($date)) {
            return false;
        }
        if ($this->zip !== null && $zip !== null && $this->zip !== $zip) {
            return false;
        }
        return true;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOnDate(Builder $query, Carbon $date): Builder
    {
        return $query->where('blackout_date', $date->toDateString());
    }

    public function scopeForZip(Builder $query, ?string $zip): Builder
    {
        return $query->where(static function (Builder $q) use ($zip) {
            $q->whereNull('zip');
            if ($zip !== null) {
                $q->orWhere('zip', $zip);
            }
        });
    }
}
