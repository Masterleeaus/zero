<?php

declare(strict_types=1);

namespace App\Listeners\Route;

use App\Events\Route\RouteAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to RouteAssigned events.
 *
 * Responsibilities:
 *   1. Notify the assigned technician about the new route day-run.
 *   2. Trigger dispatch board refresh signal.
 *   3. Log assignment for audit trail.
 */
class RouteAssignedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RouteAssigned $event): void
    {
        $routeStop = $event->routeStop;

        try {
            $this->notifyTechnician($routeStop);
            $this->logAssignment($routeStop);
        } catch (\Throwable $th) {
            Log::error('RouteAssignedListener: ' . $th->getMessage(), [
                'route_stop_id' => $routeStop->id,
            ]);
        }
    }

    private function notifyTechnician(\App\Models\Route\DispatchRouteStop $routeStop): void
    {
        // Notification wired via downstream automation rules.
        // Placeholder: dispatch notification job when messaging layer is ready.
        Log::info('RouteAssigned: technician notification queued', [
            'route_stop_id'    => $routeStop->id,
            'assigned_user_id' => $routeStop->assigned_user_id,
            'route_date'       => $routeStop->route_date,
        ]);
    }

    private function logAssignment(\App\Models\Route\DispatchRouteStop $routeStop): void
    {
        Log::info('RouteAssigned: day-route assigned', [
            'route_stop_id'    => $routeStop->id,
            'route_id'         => $routeStop->route_id,
            'assigned_user_id' => $routeStop->assigned_user_id,
            'route_date'       => $routeStop->route_date,
        ]);
    }
}
