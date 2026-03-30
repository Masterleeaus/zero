<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\Money\QuoteItem;
use App\Models\Money\Payment;
use App\Models\Money\Quote;
use App\Models\Work\Checklist;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Money\QuoteService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_updates_balance_and_status(): void
    {
        $user = User::factory()->create(['company_id' => 33]);
        $invoice = Invoice::factory()->create([
            'company_id' => 33,
            'total'      => 100,
            'balance'    => 100,
        ]);

        $this->actingAs($user);

        Payment::create([
            'company_id' => 33,
            'created_by' => $user->id,
            'invoice_id' => $invoice->id,
            'amount'     => 40,
        ]);

        $invoice->refresh();
        $invoice->recomputeBalance();

        $this->assertEquals(40.00, (float) $invoice->paid_amount);
        $this->assertEquals(60.00, (float) $invoice->balance);
    }

    public function test_quote_conversion_copies_checklist_template(): void
    {
        $user = User::factory()->create(['company_id' => 44]);
        $customer = Customer::factory()->create(['company_id' => 44]);
        $site = Site::factory()->create(['company_id' => 44]);

        $quote = Quote::factory()->create([
            'company_id'          => 44,
            'customer_id'         => $customer->id,
            'site_id'             => $site->id,
            'checklist_template'  => ['Task A', 'Task B'],
        ]);

        $service = app(QuoteService::class);
        $job = $service->convertToServiceJob($quote, $site->id, $user);

        $this->assertInstanceOf(ServiceJob::class, $job);
        $this->assertEquals(2, Checklist::where('service_job_id', $job->id)->count());
        $this->assertEquals($quote->company_id, $job->company_id);
    }

    public function test_quote_to_invoice_conversion_copies_items(): void
    {
        $user = User::factory()->create(['company_id' => 55]);
        $quote = Quote::factory()->create([
            'company_id'  => 55,
            'status'      => 'accepted',
            'quote_number'=> 'Q-INV-1',
            'subtotal'    => 100,
            'tax'         => 10,
            'total'       => 110,
        ]);

        QuoteItem::create([
            'company_id' => 55,
            'created_by' => $user->id,
            'quote_id'   => $quote->id,
            'description'=> 'Work',
            'quantity'   => 1,
            'unit_price' => 100,
            'tax_rate'   => 10,
            'line_total' => 100,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.money.quotes.convert-invoice', $quote))
            ->assertRedirect();

        $invoice = Invoice::where('quote_id', $quote->id)->firstOrFail();
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals(110.00, (float) $invoice->total);
    }

    public function test_totals_are_recomputed_from_items(): void
    {
        $user = User::factory()->create(['company_id' => 77]);
        $customer = Customer::factory()->create(['company_id' => 77]);

        $this->actingAs($user)
            ->post(route('dashboard.money.quotes.store'), [
                'quote_number' => 'Q-RAW',
                'customer_id'  => $customer->id,
                'currency'     => 'USD',
                'items' => [
                    ['description' => 'A', 'quantity' => 2, 'unit_price' => 50, 'tax_rate' => 10],
                    ['description' => 'B', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0],
                ],
                'subtotal' => 1,
                'tax' => 1,
                'total' => 1,
            ])->assertRedirect();

        $quote = Quote::where('quote_number', 'Q-RAW')->firstOrFail();
        $this->assertEquals(200.0, (float) $quote->subtotal);
        $this->assertEquals(10.0, (float) $quote->tax);
        $this->assertEquals(210.0, (float) $quote->total);
    }

    public function test_payment_updates_invoice_status_and_balance(): void
    {
        $user = User::factory()->create(['company_id' => 88]);
        $invoice = Invoice::factory()->create([
            'company_id' => 88,
            'subtotal' => 100,
            'tax' => 0,
            'total' => 100,
            'balance' => 100,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.money.payments.store', $invoice), [
                'amount' => 100,
            ])->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(0.0, (float) $invoice->balance);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_numbering_is_company_scoped_unique(): void
    {
        $user = User::factory()->create(['company_id' => 99]);
        $customer = Customer::factory()->create(['company_id' => 99]);

        $this->actingAs($user)
            ->post(route('dashboard.money.quotes.store'), [
                'customer_id' => $customer->id,
                'quote_number' => null,
                'currency' => 'USD',
                'items' => [
                    ['description' => 'X', 'quantity' => 1, 'unit_price' => 10],
                ],
            ])->assertRedirect();

        $this->actingAs($user)
            ->post(route('dashboard.money.quotes.store'), [
                'customer_id' => $customer->id,
                'quote_number' => null,
                'currency' => 'USD',
                'items' => [
                    ['description' => 'Y', 'quantity' => 1, 'unit_price' => 20],
                ],
            ])->assertRedirect();

        $numbers = Quote::where('company_id', 99)->pluck('quote_number')->toArray();
        $this->assertCount(2, array_unique($numbers));
    }
}
