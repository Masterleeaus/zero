<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CoreRoutesTest extends TestCase
{
    public function test_core_routes_are_registered(): void
    {
        $routes = [
            'dashboard.crm.customers.index',
            'dashboard.crm.customers.show',
            'dashboard.crm.enquiries.index',
            'dashboard.crm.enquiries.show',
            'dashboard.work.sites.index',
            'dashboard.work.sites.show',
            'dashboard.work.service-jobs.index',
            'dashboard.work.service-jobs.show',
            'dashboard.work.checklists.index',
            'dashboard.work.checklists.show',
            'dashboard.money.quotes.index',
            'dashboard.money.quotes.show',
            'dashboard.money.invoices.index',
            'dashboard.money.invoices.show',
            'dashboard.team.roster.index',
            'dashboard.insights.overview',
            'dashboard.insights.reports',
        ];

        foreach ($routes as $name) {
            $this->assertTrue(Route::has($name), sprintf('Route [%s] should be registered', $name));
        }
    }

    public function test_product_is_tenant_scoped(): void
    {
        $this->assertContains(
            \App\Models\Concerns\BelongsToCompany::class,
            class_uses_recursive(Product::class)
        );
    }
}
