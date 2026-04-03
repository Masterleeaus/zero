<?php

declare(strict_types=1);

namespace App\Events\Route;

use App\Models\Route\DispatchRouteStop;

class RouteAssigned
{
    public function __construct(public readonly DispatchRouteStop $routeStop) {}
}
