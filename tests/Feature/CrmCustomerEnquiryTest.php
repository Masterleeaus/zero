<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmCustomerEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_scoped_by_company(): void
    {
        $user = User::factory()->create(['company_id' => 10]);
        $other = Customer::factory()->create(['company_id' => 20]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.crm.customers.store'), [
            'name' => 'Scoped Customer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'name'       => 'Scoped Customer',
            'company_id' => 10,
        ]);

        $this->assertDatabaseCount('customers', 2);
        $this->assertSame(20, $other->company_id);
    }

    public function test_enquiry_links_customer_and_scopes(): void
    {
        $user = User::factory()->create(['company_id' => 10]);
        $customer = Customer::factory()->create(['company_id' => 10]);

        $this->actingAs($user);

        $response = $this->post(route('dashboard.crm.enquiries.store'), [
            'name'        => 'Website form',
            'customer_id' => $customer->id,
            'status'      => 'open',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('enquiries', [
            'name'        => 'Website form',
            'customer_id' => $customer->id,
            'company_id'  => 10,
        ]);
    }
}
