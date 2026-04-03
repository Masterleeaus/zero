<?php

declare(strict_types=1);

namespace App\Services\Route;

use App\Contracts\SchedulableEntity;
use App\Models\Route\DispatchRoute;
use App\Models\Route\DispatchRouteStop;
use App\Models\Route\DispatchRouteStopItem;
use App\Models\Route\RouteBlackoutDay;
use App\Models\Route\TechnicianAvailability;
use App\Models\Route\AvailabilityWindow;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * RouteService — canonical route feasibility and assignment service.
 *
 * STAGE D helpers:
 *   canAssignToRoute()          — check all route assignment pre-conditions
 *   canFitInAvailabilityWindow() — check time fits within technician's window
 *   hasRouteConflict()          — check for scheduling conflicts on a route
 *   travelWindowFeasible()      — placeholder for future travel-time checks
 *   capacityRemaining()         — stops remaining on a day-route
 *   availabilitySummary()       — structured summary of technician availability
 *
 * STAGE B helper:
 *   addStopToRoute()            — add a schedulable entity to a day-route
 *   findOrCreateRouteStop()     — find or create a DispatchRouteStop for date
 */
class RouteService
{
    // ── STAGE D — Feasibility checks ─────────────────────────────────────────

    /**
     * Check all conditions required to assign a schedulable entity to a route stop.
     *
     * Returns an array with:
     *   ok      bool   — true if assignment is feasible
     *   reasons list<string> — reasons for failure (empty when ok=true)
     *
     * @return array{ok: bool, reasons: list<string>}
     */
    public function canAssignToRoute(
        DispatchRoute $route,
        Carbon $date,
        ?int $userId = null,
        ?string $premisesZip = null,
    ): array {
        $reasons = [];

        // 1. Route must be active
        if ($route->status !== 'active') {
            $reasons[] = "Route '{$route->name}' is not active.";
        }

        // 2. Route must run on this day of week
        if (! $route->runsOn($date)) {
            $dayName  = $date->format('l');
            $reasons[] = "Route '{$route->name}' does not run on {$dayName}.";
        }

        // 3. No blackout on this date for the route/zip combination
        $blackoutConflict = $this->getBlackoutConflict($route, $date, $premisesZip);
        if ($blackoutConflict !== null) {
            $reasons[] = "Date {$date->toDateString()} is a blackout day: {$blackoutConflict->reason}.";
        }

        // 4. Day-route capacity check
        $routeStop = DispatchRouteStop::where('route_id', $route->id)
            ->where('route_date', $date->toDateString())
            ->first();

        if ($routeStop !== null && ! $routeStop->hasCapacity()) {
            $reasons[] = "Day route for {$date->toDateString()} has no remaining capacity.";
        }

        // 5. Technician availability check (if user provided)
        if ($userId !== null) {
            $availability = TechnicianAvailability::where('user_id', $userId)
                ->effectiveOn($date)
                ->first();

            if ($availability === null) {
                $reasons[] = "No active availability schedule found for user #{$userId}.";
            } elseif (! $availability->isActiveOn($date)) {
                $reasons[] = "User #{$userId} is not scheduled to work on {$date->format('l')}.";
            } else {
                // Check for blocking windows
                $blocking = AvailabilityWindow::where('user_id', $userId)
                    ->blocking()
                    ->onDate($date)
                    ->exists();

                if ($blocking) {
                    $reasons[] = "User #{$userId} has a blocking availability window on {$date->toDateString()}.";
                }
            }
        }

        return ['ok' => empty($reasons), 'reasons' => $reasons];
    }

    /**
     * Check whether a scheduled time fits within a technician's availability window.
     *
     * @return array{ok: bool, reasons: list<string>}
     */
    public function canFitInAvailabilityWindow(
        int $userId,
        Carbon $scheduledStart,
        Carbon $scheduledEnd,
    ): array {
        $reasons      = [];
        $date         = $scheduledStart->copy()->startOfDay();
        $availability = TechnicianAvailability::where('user_id', $userId)
            ->effectiveOn($date)
            ->first();

        if ($availability === null) {
            return ['ok' => false, 'reasons' => ["No availability schedule for user #{$userId}."]];
        }

        $window = $availability->workWindowFor($date);
        if ($window === null) {
            $reasons[] = "User #{$userId} does not work on {$date->format('l')}.";
            return ['ok' => false, 'reasons' => $reasons];
        }

        if ($scheduledStart->lt($window['start'])) {
            $reasons[] = "Scheduled start is before work start ({$window['start']->toTimeString()}).";
        }
        if ($scheduledEnd->gt($window['end'])) {
            // Allow up to overtime
            $overtimeEnd = $window['end']->copy()->addHours((float) $availability->max_overtime_hours);
            if ($scheduledEnd->gt($overtimeEnd)) {
                $reasons[] = "Scheduled end exceeds work window including overtime ({$overtimeEnd->toTimeString()}).";
            }
        }

        // Check blocking windows that overlap the scheduled slot
        $blocking = AvailabilityWindow::where('user_id', $userId)
            ->blocking()
            ->onDate($date)
            ->get();

        foreach ($blocking as $window) {
            $blockStart = $date->copy()->setTimeFromTimeString($window->start_time);
            $blockEnd   = $date->copy()->setTimeFromTimeString($window->end_time);

            if ($scheduledStart->lt($blockEnd) && $scheduledEnd->gt($blockStart)) {
                $reasons[] = "Conflicts with blocking window ({$window->window_type}: {$window->start_time}–{$window->end_time}).";
            }
        }

        return ['ok' => empty($reasons), 'reasons' => $reasons];
    }

    /**
     * Check for scheduling conflicts on a route stop for a given date.
     *
     * A conflict exists when:
     *   - the route has a blackout on the date
     *   - the assigned technician is unavailable
     *
     * @return array{ok: bool, reasons: list<string>}
     */
    public function hasRouteConflict(DispatchRouteStop $routeStop): array
    {
        $route  = $routeStop->route;
        $date   = Carbon::parse($routeStop->route_date);
        $userId = $routeStop->assigned_user_id ?? $route?->assigned_user_id;

        return $this->canAssignToRoute($route, $date, $userId);
    }

    /**
     * Travel window feasibility placeholder.
     *
     * Currently returns ok=true; can be extended with Google Maps / OSRM
     * travel time estimation when travel data is available.
     *
     * @return array{ok: bool, reasons: list<string>}
     */
    public function travelWindowFeasible(
        int $fromPremisesId,
        int $toPremisesId,
        Carbon $departAt,
        int $bufferMinutes = 15,
    ): array {
        // Placeholder — always feasible until travel-time provider is integrated.
        return ['ok' => true, 'reasons' => []];
    }

    /**
     * Return remaining capacity for a route stop, or null if unlimited.
     */
    public function capacityRemaining(DispatchRouteStop $routeStop): ?int
    {
        return $routeStop->capacityRemaining();
    }

    /**
     * Return a structured availability summary for a user on a given date.
     *
     * @return array{
     *   user_id: int,
     *   date: string,
     *   is_scheduled: bool,
     *   work_start: string|null,
     *   work_end: string|null,
     *   blocking_windows: list<array<string, mixed>>,
     * }
     */
    public function availabilitySummary(int $userId, Carbon $date): array
    {
        $availability = TechnicianAvailability::where('user_id', $userId)
            ->effectiveOn($date)
            ->first();

        if ($availability === null) {
            return [
                'user_id'          => $userId,
                'date'             => $date->toDateString(),
                'is_scheduled'     => false,
                'work_start'       => null,
                'work_end'         => null,
                'blocking_windows' => [],
            ];
        }

        $window   = $availability->workWindowFor($date);
        $blocking = AvailabilityWindow::where('user_id', $userId)
            ->blocking()
            ->onDate($date)
            ->get()
            ->map(static fn ($w) => [
                'type'       => $w->window_type,
                'start_time' => $w->start_time,
                'end_time'   => $w->end_time,
                'reason'     => $w->reason,
            ])
            ->values()
            ->all();

        return [
            'user_id'          => $userId,
            'date'             => $date->toDateString(),
            'is_scheduled'     => $window !== null,
            'work_start'       => $window !== null ? $window['start']->toTimeString() : null,
            'work_end'         => $window !== null ? $window['end']->toTimeString() : null,
            'blocking_windows' => $blocking,
        ];
    }

    // ── STAGE B — Route-stop management ──────────────────────────────────────

    /**
     * Find an existing DispatchRouteStop for (route, date) or create one.
     *
     * Respects company scoping via BelongsToCompany global scope.
     */
    public function findOrCreateRouteStop(
        DispatchRoute $route,
        Carbon $date,
        ?int $userId = null,
    ): DispatchRouteStop {
        $dateStr = $date->toDateString();
        $stop    = DispatchRouteStop::where('route_id', $route->id)
            ->where('route_date', $dateStr)
            ->first();

        if ($stop !== null) {
            return $stop;
        }

        return DispatchRouteStop::create([
            'company_id'       => $route->company_id,
            'route_id'         => $route->id,
            'route_date'       => $dateStr,
            'assigned_user_id' => $userId ?? $route->assigned_user_id,
            'team_id'          => $route->team_id,
            'planned_start_at' => $date->copy()->setTimeFromTimeString('08:00:00'),
            'status'           => 'draft',
        ]);
    }

    /**
     * Add a schedulable entity as an ordered stop on a day-route.
     *
     * Checks capacity and blackout before adding.
     *
     * @param  SchedulableEntity&\Illuminate\Database\Eloquent\Model  $entity
     * @return array{ok: bool, stop_item: DispatchRouteStopItem|null, reasons: list<string>}
     */
    public function addStopToRoute(
        DispatchRoute $route,
        Carbon $date,
        SchedulableEntity $entity,
        ?string $premisesZip = null,
        ?int $premisesId = null,
        ?int $customerId = null,
    ): array {
        $userId = $entity->getAssignedUserId();

        $check = $this->canAssignToRoute($route, $date, $userId, $premisesZip);
        if (! $check['ok']) {
            return ['ok' => false, 'stop_item' => null, 'reasons' => $check['reasons']];
        }

        $routeStop = $this->findOrCreateRouteStop($route, $date, $userId);

        $nextSequence = $routeStop->stopItems()->max('sequence') + 1;

        $stopItem = DispatchRouteStopItem::create([
            'company_id'       => $route->company_id,
            'route_stop_id'    => $routeStop->id,
            'schedulable_type' => $entity->getSchedulableType(),
            'schedulable_id'   => $entity->getKey(),
            'sequence'         => $nextSequence,
            'premises_id'      => $premisesId,
            'customer_id'      => $customerId,
            'status'           => 'pending',
        ]);

        return ['ok' => true, 'stop_item' => $stopItem, 'reasons' => []];
    }

    // ── STAGE C — Availability queries ───────────────────────────────────────

    /**
     * Return all availability windows for a user on a given date.
     *
     * @return Collection<int, AvailabilityWindow>
     */
    public function getWindowsForUser(int $userId, Carbon $date): Collection
    {
        return AvailabilityWindow::where('user_id', $userId)
            ->onDate($date)
            ->get();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getBlackoutConflict(
        DispatchRoute $route,
        Carbon $date,
        ?string $zip,
    ): ?RouteBlackoutDay {
        $groupIds = $route->blackoutGroups()->pluck('route_blackout_groups.id');
        if ($groupIds->isEmpty()) {
            return null;
        }

        return RouteBlackoutDay::whereIn('blackout_group_id', $groupIds)
            ->onDate($date)
            ->forZip($zip)
            ->first();
    }
}
