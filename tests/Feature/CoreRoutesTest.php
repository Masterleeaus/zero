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
            'dashboard.crm.customers.contacts.index',
            'dashboard.crm.customers.notes.index',
            'dashboard.crm.customers.documents.index',
            'dashboard.crm.enquiries.index',
            'dashboard.crm.enquiries.show',
            'dashboard.crm.deals.index',
            'dashboard.crm.deals.kanban',
            'dashboard.crm.deals.status',
            'dashboard.crm.deals.notes.index',
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
            'dashboard.money.quote-templates.index',
            'dashboard.money.credit-notes.index',
            'dashboard.money.credit-notes.apply-invoice',
            'dashboard.money.bank-accounts.index',
            'dashboard.money.bank-accounts.set-default',
            'dashboard.money.taxes.index',
            'dashboard.money.taxes.set-default',
            'dashboard.team.roster.index',
            'dashboard.team.zones.index',
            'dashboard.team.service-area-regions.index',
            'dashboard.team.service-area-districts.index',
            'dashboard.team.service-area-branches.index',
            'dashboard.team.cleaners.show',
            'dashboard.team.timesheets.index',
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
