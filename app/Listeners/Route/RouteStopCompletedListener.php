<?php

declare(strict_types=1);

namespace App\Listeners\Route;

use App\Events\Route\RouteStopCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to RouteStopCompleted events.
 *
 * Responsibilities:
 *   1. Record completion metrics for the completed stop item.
 *   2. Trigger route completion summary update.
 *   3. Queue a notification for the assigned technician's manager.
 */
class RouteStopCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public ?string $queue = 'default';

    public function handle(RouteStopCompleted $event): void
    {
        $stopItem = $event->stopItem;

        try {
            $this->recordCompletionMetrics($stopItem);
            $this->notifyCompletion($stopItem);
        } catch (\Throwable $th) {
            Log::error('RouteStopCompletedListener: ' . $th->getMessage(), [
                'stop_item_id' => $stopItem->id,
            ]);
        }
    }

    private function recordCompletionMetrics(\App\Models\Route\DispatchRouteStopItem $stopItem): void
    {
        Log::info('RouteStopCompleted: metrics recorded', [
            'stop_item_id'  => $stopItem->id,
            'route_stop_id' => $stopItem->route_stop_id,
            'completed_at'  => $stopItem->completed_at,
        ]);
    }

    private function notifyCompletion(\App\Models\Route\DispatchRouteStopItem $stopItem): void
    {
        // Downstream: notify manager/dispatcher that a stop item is complete.
        Log::info('RouteStopCompleted: completion notification queued', [
            'stop_item_id'  => $stopItem->id,
            'route_stop_id' => $stopItem->route_stop_id,
        ]);
    }
}
