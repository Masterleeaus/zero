<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MenuRoutesTest extends TestCase
{
    /** @test */
    public function menu_routes_exist_for_seeded_entries(): void
    {
        $expectedRoutes = [
            'dashboard.crm.customers.index',
            'dashboard.work.sites.index',
            'dashboard.work.service-jobs.index',
            'dashboard.money.quotes.index',
            'dashboard.money.invoices.index',
            'dashboard.insights.overview',
        ];

        foreach ($expectedRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Expected menu route [{$route}] to be registered."
            );
        }
    }
}
