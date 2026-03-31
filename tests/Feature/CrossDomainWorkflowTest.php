<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Crm\Customer;
use App\Models\Crm\Enquiry;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\User;
use App\Models\Work\ServiceJob;
use App\Models\Work\Site;
use App\Services\Money\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossDomainWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_model_binding_respects_company_scope(): void
    {
        $other = User::factory()->create(['company_id' => 2]);
        $quoteOther = Quote::factory()->create(['company_id' => 2]);
        $invoiceOther = Invoice::factory()->create(['company_id' => 2]);
        $jobOther = ServiceJob::factory()->create(['company_id' => 2]);

        $user = User::factory()->create(['company_id' => 1]);
        $this->actingAs($user);

        $this->get(route('dashboard.money.quotes.show', $quoteOther))->assertNotFound();
        $this->get(route('dashboard.money.invoices.show', $invoiceOther))->assertNotFound();
        $this->get(route('dashboard.work.service-jobs.show', $jobOther))->assertNotFound();
    }

    public function test_quote_to_service_job_conversion_copies_customer_and_checklist(): void
    {
        $user = User::factory()->create(['company_id' => 10]);
        $customer = Customer::factory()->create(['company_id' => 10]);
        $site = Site::factory()->create(['company_id' => 10]);

        $quote = Quote::factory()->create([
            'company_id' => 10,
            'customer_id'=> $customer->id,
            'checklist_template' => ['Prep', 'Finish'],
            'status' => 'accepted',
        ]);

        $this->actingAs($user);
        $service = app(QuoteService::class);
        $job = $service->convertToServiceJob($quote, $site->id, $user);

        $this->assertEquals($customer->id, $job->customer_id);
        $this->assertEquals($site->id, $job->site_id);
        $this->assertEquals(2, $job->checklists()->count());
        $this->assertEquals($quote->id, $job->quote_id);
    }

    public function test_quote_to_invoice_conversion_keeps_items_and_totals(): void
    {
        $user = User::factory()->create(['company_id' => 11]);
        $customer = Customer::factory()->create(['company_id' => 11]);

        $quote = Quote::factory()->create([
            'company_id' => 11,
            'customer_id'=> $customer->id,
            // Use 'sent' to cover the allowed conversion path alongside the approved status.
            'status'     => 'sent',
            'subtotal'   => 100,
            'tax'        => 10,
            'total'      => 110,
        ]);

        QuoteItem::create([
            'company_id' => 11,
            'created_by' => $user->id,
            'quote_id'   => $quote->id,
            'description'=> 'Work',
            'quantity'   => 2,
            'unit_price' => 50,
            'tax_rate'   => 10,
            'line_total' => 100,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('dashboard.money.quotes.convert-invoice', $quote))
            ->assertRedirect();

        $invoice = Invoice::where('quote_id', $quote->id)->firstOrFail();
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertEquals(100.0, (float) $invoice->subtotal);
        $this->assertEquals(10.0, (float) $invoice->tax);
        $this->assertEquals(110.0, (float) $invoice->total);
        $this->assertEquals('draft', $invoice->status);
        $this->assertEquals(Quote::STATUS_CONVERTED, $quote->fresh()->status);
    }

    public function test_payment_updates_balance_and_status(): void
    {
        $user = User::factory()->create(['company_id' => 12]);
        $invoice = Invoice::factory()->create([
            'company_id' => 12,
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

    public function test_insights_overview_loads_for_company(): void
    {
        $user = User::factory()->create(['company_id' => 13]);
        Customer::factory()->create(['company_id' => 13]);
        Enquiry::factory()->create(['company_id' => 13, 'customer_id' => Customer::factory(['company_id' => 13])]);
        Site::factory()->create(['company_id' => 13]);
        ServiceJob::factory()->create(['company_id' => 13]);
        Quote::factory()->create(['company_id' => 13]);
        Invoice::factory()->create(['company_id' => 13]);

        $this->actingAs($user)
            ->get(route('dashboard.insights.overview'))
            ->assertOk()
            ->assertSee('Insights Overview');
    }
}
