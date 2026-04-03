<?php

declare(strict_types=1);

namespace App\Events\Route;

use App\Models\Route\DispatchRoute;
use App\Models\Route\DispatchRouteStop;

class RouteConflictDetected
{
    /**
     * @param  list<string>  $reasons
     */
    public function __construct(
        public readonly DispatchRoute $route,
        public readonly DispatchRouteStop $routeStop,
        public readonly array $reasons,
    ) {}
}
