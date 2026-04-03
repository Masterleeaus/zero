<?php

declare(strict_types=1);

namespace App\Models\Route;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RouteBlackoutGroup — a named collection of blackout days.
 *
 * Blackout groups are assigned to DispatchRoutes.  Any job on a route
 * with a matching blackout day will be flagged as a conflict.
 *
 * Merged from fieldservice_route_availability: fsm.blackout.group + fsm.blackout.day.
 */
class RouteBlackoutGroup extends Model
{
    use BelongsToCompany;

    protected $table = 'route_blackout_groups';

    protected $fillable = [
        'company_id',
        'name',
        'notes',
        'created_by',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function blackoutDays(): HasMany
    {
        return $this->hasMany(RouteBlackoutDay::class, 'blackout_group_id');
    }

    public function routes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            DispatchRoute::class,
            'dispatch_route_blackout_group',
            'route_blackout_group_id',
            'dispatch_route_id',
        );
    }
}
