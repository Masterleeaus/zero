<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyBoundaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_data_is_isolated_by_global_scope(): void
    {
        $userA = User::factory()->create(['company_id' => 41]);
        $userB = User::factory()->create(['company_id' => 42]);

        $customerA = Customer::factory()->create(['company_id' => 41]);
        $customerB = Customer::factory()->create(['company_id' => 42]);

        Quote::factory()->for($customerA, 'customer')->create(['company_id' => 41]);
        Quote::factory()->for($customerB, 'customer')->create(['company_id' => 42]);

        Invoice::factory()->for($customerA, 'customer')->create(['company_id' => 41, 'quote_id' => null]);
        Invoice::factory()->for($customerB, 'customer')->create(['company_id' => 42, 'quote_id' => null]);

        $siteA = Site::factory()->create(['company_id' => 41]);
        $siteB = Site::factory()->create(['company_id' => 42]);

        ServiceJob::factory()->create(['company_id' => 41, 'site_id' => $siteA->id]);
        ServiceJob::factory()->create(['company_id' => 42, 'site_id' => $siteB->id]);

        $this->actingAs($userA);

        $this->assertSame(1, Customer::count());
        $this->assertSame(1, Quote::count());
        $this->assertSame(1, Invoice::count());
        $this->assertSame(1, ServiceJob::count());

        $this->actingAs($userB);

        $this->assertSame(1, Customer::count());
        $this->assertSame(1, Quote::count());
        $this->assertSame(1, Invoice::count());
        $this->assertSame(1, ServiceJob::count());
    }

    public function test_admins_can_bypass_scope_when_needed(): void
    {
        Customer::factory()->create(['company_id' => 50]);
        Customer::factory()->create(['company_id' => 51]);

        Quote::factory()->for(Customer::factory(['company_id' => 50]), 'customer')->create(['company_id' => 50]);
        Quote::factory()->for(Customer::factory(['company_id' => 51]), 'customer')->create(['company_id' => 51]);

        Invoice::factory()->for(Customer::factory(['company_id' => 50]), 'customer')->create(['company_id' => 50, 'quote_id' => null]);
        Invoice::factory()->for(Customer::factory(['company_id' => 51]), 'customer')->create(['company_id' => 51, 'quote_id' => null]);

        ServiceJob::factory()->create([
            'company_id' => 50,
            'site_id'    => Site::factory(['company_id' => 50]),
        ]);
        ServiceJob::factory()->create([
            'company_id' => 51,
            'site_id'    => Site::factory(['company_id' => 51]),
        ]);

        $this->assertSame(2, Quote::withoutGlobalScope('company')->count());
        $this->assertSame(2, Invoice::withoutGlobalScope('company')->count());
        $this->assertSame(2, ServiceJob::withoutGlobalScope('company')->count());
        $this->assertSame(2, Customer::withoutGlobalScope('company')->count());
    }
}
