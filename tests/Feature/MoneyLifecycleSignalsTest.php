<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\InvoiceIssued;
use App\Events\InvoicePaid;
use App\Events\QuoteAccepted;
use App\Http\Controllers\Core\Money\QuoteController;
use App\Http\Controllers\Core\Money\InvoiceController;
use App\Http\Controllers\Core\Money\PaymentController;
use App\Models\Money\Invoice;
use App\Models\Money\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MoneyLifecycleSignalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function quote_accept_dispatches_once_per_transition(): void
    {
        Event::fake([QuoteAccepted::class]);

        $user = User::factory()->create(['company_id' => 1]);
        $quote = Quote::factory()->create(['company_id' => 1, 'status' => 'draft']);

        $this->be($user);

        $request = Request::create('/', 'POST', ['status' => 'accepted']);
        $request->setUserResolver(fn () => $user);

        app(QuoteController::class)->updateStatus($request, $quote);
        app(QuoteController::class)->updateStatus($request, $quote->fresh());

        Event::assertDispatchedTimes(QuoteAccepted::class, 1);
    }

    /** @test */
    public function invoice_issued_and_paid_events_fire_only_on_transition(): void
    {
        Event::fake([InvoiceIssued::class, InvoicePaid::class]);

        $user = User::factory()->create(['company_id' => 1]);
        $invoice = Invoice::factory()->create(['company_id' => 1, 'status' => 'draft', 'balance' => 50, 'total' => 50]);

        $this->be($user);

        $request = Request::create('/', 'POST', ['status' => 'issued']);
        $request->setUserResolver(fn () => $user);

        app(InvoiceController::class)->update($request, $invoice);
        app(InvoiceController::class)->update($request, $invoice->fresh());

        Event::assertDispatchedTimes(InvoiceIssued::class, 1);
        Event::assertDispatchedTimes(InvoicePaid::class, 0);
    }

    /** @test */
    public function partial_payments_do_not_emit_paid_and_full_payoff_emits_once(): void
    {
        Event::fake([InvoicePaid::class]);

        $user = User::factory()->create(['company_id' => 1]);
        $invoice = Invoice::factory()->create([
            'company_id'  => 1,
            'status'      => 'issued',
            'total'       => 100,
            'paid_amount' => 0,
            'balance'     => 100,
        ]);

        $this->be($user);

        $first = Request::create('/', 'POST', ['amount' => 50]);
        $first->setUserResolver(fn () => $user);
        app(PaymentController::class)->store($first, $invoice);

        $second = Request::create('/', 'POST', ['amount' => 50]);
        $second->setUserResolver(fn () => $user);
        app(PaymentController::class)->store($second, $invoice->fresh());

        Event::assertDispatchedTimes(InvoicePaid::class, 1);
    }
}
