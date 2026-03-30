<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
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
}
