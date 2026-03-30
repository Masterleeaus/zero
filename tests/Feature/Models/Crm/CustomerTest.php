<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Crm;

use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_are_scoped_to_company(): void
    {
        $user = User::factory()->create(['company_id' => 13]);

        Customer::factory()->create(['company_id' => 13]);
        Customer::factory()->create(['company_id' => 14]);

        $this->actingAs($user);

        $this->assertSame(1, Customer::count());
    }

    public function test_relationships_return_related_entities(): void
    {
        $customer = Customer::factory()->create(['company_id' => 21]);

        Enquiry::factory()->create([
            'company_id'  => $customer->company_id,
            'customer_id' => $customer->id,
        ]);

        $quote = Quote::factory()->create([
            'company_id'  => $customer->company_id,
            'customer_id' => $customer->id,
        ]);

        Invoice::factory()->create([
            'company_id'  => $customer->company_id,
            'customer_id' => $customer->id,
            'quote_id'    => $quote->id,
        ]);

        $customer->refresh();

        $this->assertCount(1, $customer->enquiries);
        $this->assertCount(1, $customer->quotes);
        $this->assertCount(1, $customer->invoices);
    }

    public function test_soft_deleted_customers_are_hidden_from_default_queries(): void
    {
        $customer = Customer::factory()->create(['company_id' => 30]);

        $customer->delete();

        $this->assertSame(0, Customer::count());
        $this->assertTrue(Customer::withTrashed()->whereKey($customer->id)->exists());
    }
}
