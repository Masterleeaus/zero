<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Money;

use App\Events\InvoiceIssued;
use App\Events\InvoicePaid;
use App\Http\Controllers\Core\Money\InvoiceController;
use App\Http\Controllers\Core\Money\PaymentController;
use App\Models\Crm\Customer;
use App\Models\Money\Invoice;
use App\Models\Money\InvoiceItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_number_is_unique_per_company(): void
    {
        Invoice::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id'    => 1,
            'invoice_number'=> 'INV-100',
            'quote_id'      => null,
        ]);

        Invoice::factory()->for(Customer::factory(['company_id' => 2]), 'customer')->create([
            'company_id'    => 2,
            'invoice_number'=> 'INV-100',
            'quote_id'      => null,
        ]);

        $this->expectException(QueryException::class);

        Invoice::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id'    => 1,
            'invoice_number'=> 'INV-100',
        ]);
    }

    public function test_recompute_balance_uses_payments(): void
    {
        $invoice = Invoice::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id'  => 1,
            'total'       => 150,
            'balance'     => 150,
            'paid_amount' => 0,
            'quote_id'    => null,
        ]);

        \App\Models\Money\Payment::factory()->create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'amount'     => 50,
        ]);
        \App\Models\Money\Payment::factory()->create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'amount'     => 25,
        ]);

        $invoice->refresh();
        $invoice->recomputeBalance();
        $invoice->refresh();

        $this->assertEquals(75.0, (float) $invoice->paid_amount);
        $this->assertEquals(75.0, (float) $invoice->balance);
    }

    public function test_status_transitions_cover_lifecycle_paths(): void
    {
        $user = User::factory()->create(['company_id' => 3]);
        $customer = Customer::factory()->create(['company_id' => 3]);

        $issued = Invoice::factory()->for($customer, 'customer')->create([
            'company_id' => 3,
            'status'     => 'draft',
            'quote_id'   => null,
        ]);

        $this->actingAs($user);
        $controller = app(InvoiceController::class);

        $issueRequest = Request::create('/', 'POST', ['status' => 'issued']);
        $issueRequest->setUserResolver(fn () => $user);
        $controller->update($issueRequest, $issued);
        $this->assertSame('issued', $issued->fresh()->status);

        $paidRequest = Request::create('/', 'POST', ['status' => 'paid']);
        $paidRequest->setUserResolver(fn () => $user);
        $controller->update($paidRequest, $issued->fresh());
        $this->assertSame('paid', $issued->fresh()->status);

        $overdue = Invoice::factory()->for($customer, 'customer')->create([
            'company_id' => 3,
            'status'     => 'draft',
            'quote_id'   => null,
        ]);
        $controller->markOverdue($overdue);
        $this->assertSame('overdue', $overdue->fresh()->status);

        $voided = Invoice::factory()->for($customer, 'customer')->create([
            'company_id' => 3,
            'status'     => 'issued',
            'quote_id'   => null,
        ]);
        $voidRequest = Request::create('/', 'POST', ['status' => 'void']);
        $voidRequest->setUserResolver(fn () => $user);
        $controller->update($voidRequest, $voided);
        $this->assertSame('void', $voided->fresh()->status);
    }

    public function test_invoice_events_fire_on_transitions(): void
    {
        $user = User::factory()->create(['company_id' => 4]);
        $customer = Customer::factory()->create(['company_id' => 4]);

        $invoice = Invoice::factory()->for($customer, 'customer')->create([
            'company_id' => 4,
            'status'     => 'draft',
            'total'      => 50,
            'balance'    => 50,
            'quote_id'   => null,
        ]);

        $this->actingAs($user);
        Event::fake([InvoiceIssued::class, InvoicePaid::class]);

        $issueRequest = Request::create('/', 'POST', ['status' => 'issued']);
        $issueRequest->setUserResolver(fn () => $user);
        app(InvoiceController::class)->update($issueRequest, $invoice);

        Event::assertDispatchedTimes(InvoiceIssued::class, 1);
        Event::assertDispatchedTimes(InvoicePaid::class, 0);

        $paymentRequest = Request::create('/', 'POST', ['amount' => 50]);
        $paymentRequest->setUserResolver(fn () => $user);
        app(PaymentController::class)->store($paymentRequest, $invoice->fresh());

        Event::assertDispatchedTimes(InvoicePaid::class, 1);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_invoice_relationships_return_items_and_payments(): void
    {
        $invoice = Invoice::factory()->for(Customer::factory(['company_id' => 6]), 'customer')->create([
            'company_id' => 6,
            'quote_id'   => null,
        ]);

        InvoiceItem::factory()->count(2)->create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
        ]);
        \App\Models\Money\Payment::factory()->count(2)->create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
        ]);

        $invoice->refresh();

        $this->assertCount(2, $invoice->items);
        $this->assertCount(2, $invoice->payments);
    }
}
