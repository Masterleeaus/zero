<?php

declare(strict_types=1);

namespace App\Services\Dispatch;

/**
 * RouteBoardCardDTO — summary card for a DispatchRouteStop (day-route run).
 *
 * Used by the EasyDispatch board to render route header rows showing the
 * route name, date, assigned technician, stop count, and capacity.
 *
 * Module 10 (fieldservice_route) — STAGE F dispatch board compatibility.
 */
final class RouteBoardCardDTO
{
    public function __construct(
        /** Primary key of the DispatchRouteStop */
        public readonly int $routeStopId,

        /** Primary key of the parent DispatchRoute */
        public readonly int $routeId,

        /** Human-readable route name */
        public readonly string $routeName,

        /** Y-m-d date for this route run */
        public readonly string $routeDate,

        /** Assigned technician user ID (or null) */
        public readonly ?int $assignedUserId,

        /** Assigned team ID (or null) */
        public readonly ?int $teamId,

        /** Route stop status (draft | confirmed | in_progress | completed | cancelled) */
        public readonly string $status,

        /** ISO-8601 planned start datetime (or null) */
        public readonly ?string $plannedStart,

        /** ISO-8601 planned end datetime (or null) */
        public readonly ?string $plannedEnd,

        /** Number of stop items on this day-route */
        public readonly int $stopCount,

        /** Remaining capacity (null = unlimited) */
        public readonly ?int $capacityRemaining,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'route_stop_id'      => $this->routeStopId,
            'route_id'           => $this->routeId,
            'route_name'         => $this->routeName,
            'route_date'         => $this->routeDate,
            'assigned_user_id'   => $this->assignedUserId,
            'team_id'            => $this->teamId,
            'status'             => $this->status,
            'planned_start'      => $this->plannedStart,
            'planned_end'        => $this->plannedEnd,
            'stop_count'         => $this->stopCount,
            'capacity_remaining' => $this->capacityRemaining,
        ];
    }
}
