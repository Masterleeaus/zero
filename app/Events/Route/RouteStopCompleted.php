<?php

declare(strict_types=1);

namespace App\Events\Route;

use App\Models\Route\DispatchRouteStopItem;

class RouteStopCompleted
{
    public function __construct(public readonly DispatchRouteStopItem $stopItem) {}
}
