<?php

declare(strict_types=1);

namespace App\Events\Route;

use App\Models\Route\DispatchRoute;

class RouteCreated
{
    public function __construct(public readonly DispatchRoute $route) {}
}
