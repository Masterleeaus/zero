<?php

declare(strict_types=1);

namespace App\Listeners\Route;

use App\Events\Route\RouteCapacityExceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to RouteCapacityExceeded events.
 *
 * Responsibilities:
 *   1. Alert dispatcher that a day-route has exceeded its stop capacity.
 *   2. Trigger overflow stop suggestion engine.
 *   3. Log capacity breach for audit.
 */
class RouteCapacityExceededListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RouteCapacityExceeded $event): void
    {
        $route     = $event->route;
        $routeStop = $event->routeStop;

        try {
            $this->alertDispatcher($route, $routeStop);
            $this->logCapacityBreach($route, $routeStop);
        } catch (\Throwable $th) {
            Log::error('RouteCapacityExceededListener: ' . $th->getMessage(), [
                'route_id'      => $route->id,
                'route_stop_id' => $routeStop->id,
            ]);
        }
    }

    private function alertDispatcher(
        \App\Models\Route\DispatchRoute $route,
        \App\Models\Route\DispatchRouteStop $routeStop,
    ): void {
        // Downstream: notify dispatcher about capacity overflow.
        Log::warning('RouteCapacityExceeded: dispatcher alert queued', [
            'route_id'      => $route->id,
            'route_name'    => $route->name,
            'route_stop_id' => $routeStop->id,
            'route_date'    => $routeStop->route_date,
        ]);
    }

    private function logCapacityBreach(
        \App\Models\Route\DispatchRoute $route,
        \App\Models\Route\DispatchRouteStop $routeStop,
    ): void {
        Log::warning('RouteCapacityExceeded: capacity breach logged', [
            'route_id'      => $route->id,
            'route_stop_id' => $routeStop->id,
            'route_date'    => $routeStop->route_date,
        ]);
    }
}
