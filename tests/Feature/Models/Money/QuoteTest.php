<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Money;

use App\Http\Controllers\Core\Money\QuoteController;
use App\Models\Crm\Customer;
use App\Models\Money\Quote;
use App\Models\Money\QuoteItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotes_are_scoped_to_authenticated_company(): void
    {
        $companyA = 10;
        $companyB = 20;

        $userA = User::factory()->create(['company_id' => $companyA]);

        Quote::factory()->count(2)->for(Customer::factory(['company_id' => $companyA]), 'customer')->create([
            'company_id' => $companyA,
        ]);
        Quote::factory()->for(Customer::factory(['company_id' => $companyB]), 'customer')->create([
            'company_id' => $companyB,
        ]);

        $this->actingAs($userA);

        $this->assertSame(2, Quote::count());
    }

    public function test_quote_number_is_unique_per_company(): void
    {
        Quote::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id'   => 1,
            'quote_number' => 'Q-100',
        ]);

        Quote::factory()->for(Customer::factory(['company_id' => 2]), 'customer')->create([
            'company_id'   => 2,
            'quote_number' => 'Q-100',
        ]);

        $this->expectException(QueryException::class);

        Quote::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id'   => 1,
            'quote_number' => 'Q-100',
        ]);
    }

    public function test_status_transitions_follow_lifecycle(): void
    {
        $user = User::factory()->create(['company_id' => 5]);
        $quote = Quote::factory()->for(Customer::factory(['company_id' => 5]), 'customer')->create([
            'company_id' => 5,
            'status'     => 'draft',
        ]);

        $this->actingAs($user);
        $controller = app(QuoteController::class);

        $sentRequest = Request::create('/', 'POST', ['status' => 'sent']);
        $sentRequest->setUserResolver(fn () => $user);
        $controller->updateStatus($sentRequest, $quote);
        $this->assertSame('sent', $quote->fresh()->status);

        $acceptedRequest = Request::create('/', 'POST', ['status' => 'accepted']);
        $acceptedRequest->setUserResolver(fn () => $user);
        $controller->updateStatus($acceptedRequest, $quote->fresh());
        $this->assertSame('accepted', $quote->fresh()->status);

        $convertRequest = Request::create('/', 'POST');
        $convertRequest->setUserResolver(fn () => $user);
        $controller->convertToInvoice($convertRequest, $quote->fresh());

        $this->assertSame('accepted', $quote->fresh()->status, 'quote remains accepted after conversion');
        $this->assertDatabaseHas('invoices', [
            'quote_id' => $quote->id,
            'status'   => 'issued',
        ]);
    }

    public function test_accepted_scope_and_totals_from_items(): void
    {
        $quoteAccepted = Quote::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id' => 1,
            'status'     => 'accepted',
        ]);
        Quote::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id' => 1,
            'status'     => 'approved',
        ]);
        Quote::factory()->for(Customer::factory(['company_id' => 1]), 'customer')->create([
            'company_id' => 1,
            'status'     => 'sent',
        ]);

        $this->assertSame(2, Quote::accepted()->count());
        $this->assertEqualsCanonicalizing(
            ['accepted', 'approved'],
            Quote::accepted()->pluck('status')->sort()->values()->all()
        );

        $quote = Quote::factory()->for(Customer::factory(['company_id' => 2]), 'customer')->create([
            'company_id' => 2,
            'status'     => 'draft',
        ]);

        QuoteItem::factory()->create([
            'company_id' => $quote->company_id,
            'quote_id'   => $quote->id,
            'quantity'   => 2,
            'unit_price' => 50,
            'tax_rate'   => 10,
        ]);
        QuoteItem::factory()->create([
            'company_id' => $quote->company_id,
            'quote_id'   => $quote->id,
            'quantity'   => 1,
            'unit_price' => 25,
            'tax_rate'   => 0,
        ]);

        $quote->refresh();
        $quote->recomputeTotalsFromItems();
        $quote->refresh();

        $this->assertEquals(125.0, (float) $quote->subtotal);
        $this->assertEquals(10.0, (float) $quote->tax);
        $this->assertEquals(135.0, (float) $quote->total);
    }
}
