<?php

declare(strict_types=1);

namespace Tests\Feature\Money;

use App\Events\Money\SupplierBillRecorded;
use App\Events\Money\SupplierPaymentRecorded;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Money\Account;
use App\Models\Money\JournalEntry;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierBillLine;
use App\Models\Money\SupplierPayment;
use App\Models\User;
use App\Services\TitanMoney\AccountingService;
use App\Services\TitanMoney\SupplierBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Finance Domain Pass 2 — Accounts Payable tests.
 *
 * Validates:
 *   - Supplier creation (tenancy scope)
 *   - Purchase Order creation
 *   - Supplier Bill creation + journal posting
 *   - Payment recording + liability reduction
 *   - Policy enforcement
 */
class FinancePass2APTest extends TestCase
{
    use RefreshDatabase;

    private int $companyId = 42;

    private function makeUser(): User
    {
        return User::factory()->create(['company_id' => $this->companyId]);
    }

    private function makeSupplier(): Supplier
    {
        return Supplier::create([
            'company_id' => $this->companyId,
            'name'       => 'Test Supplier Ltd',
            'status'     => 'active',
        ]);
    }

    // -----------------------------------------------------------------------
    // Stage 1 — Supplier tenancy scope
    // -----------------------------------------------------------------------

    public function test_supplier_can_be_created_via_route(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('dashboard.money.suppliers.store'), [
                'name'   => 'ACME Corp',
                'email'  => 'ap@acme.com',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $this->companyId,
            'name'       => 'ACME Corp',
        ]);
    }

    public function test_supplier_is_scoped_to_company(): void
    {
        $user  = $this->makeUser();
        $other = User::factory()->create(['company_id' => 999]);

        $this->actingAs($user);

        $mySupplier    = $this->makeSupplier();
        $otherSupplier = Supplier::withoutGlobalScope('company')->create([
            'company_id' => 999,
            'name'       => 'Other Company Supplier',
            'status'     => 'active',
        ]);

        $suppliers = Supplier::all();

        $this->assertTrue($suppliers->contains($mySupplier));
        $this->assertFalse($suppliers->contains($otherSupplier));
    }

    // -----------------------------------------------------------------------
    // Stage 2 — Purchase Order creation
    // -----------------------------------------------------------------------

    public function test_purchase_order_can_be_created_via_route(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user)
            ->post(route('dashboard.money.purchase-orders.store'), [
                'po_number'    => 'PO-001',
                'supplier_id'  => $supplier->id,
                'order_date'   => '2026-04-01',
                'status'       => 'draft',
                'total_amount' => 500.00,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', [
            'company_id'  => $this->companyId,
            'po_number'   => 'PO-001',
            'supplier_id' => $supplier->id,
        ]);
    }

    public function test_purchase_order_can_store_service_job_id(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user)
            ->post(route('dashboard.money.purchase-orders.store'), [
                'po_number'      => 'PO-002',
                'supplier_id'    => $supplier->id,
                'service_job_id' => 77,
                'status'         => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('purchase_orders', [
            'po_number'      => 'PO-002',
            'service_job_id' => 77,
        ]);
    }

    // -----------------------------------------------------------------------
    // Stage 3 — Supplier Bill creation
    // -----------------------------------------------------------------------

    public function test_supplier_bill_can_be_created(): void
    {
        Event::fake([SupplierBillRecorded::class]);

        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user);

        $service = app(SupplierBillService::class);

        $bill = $service->createBill([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'due_date'    => '2026-05-01',
            'total'       => 1000.00,
            'status'      => 'awaiting_payment',
        ]);

        $this->assertInstanceOf(SupplierBill::class, $bill);
        $this->assertEquals($this->companyId, $bill->company_id);
        $this->assertEquals(1000.00, (float) $bill->total);

        Event::assertDispatched(SupplierBillRecorded::class);
    }

    public function test_supplier_bill_can_be_created_via_route(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user)
            ->post(route('dashboard.money.supplier-bills.store'), [
                'supplier_id' => $supplier->id,
                'bill_date'   => '2026-04-01',
                'due_date'    => '2026-05-01',
                'total'       => 500.00,
                'status'      => 'draft',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('supplier_bills', [
            'company_id'  => $this->companyId,
            'supplier_id' => $supplier->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // Stage 4 — Journal posting correctness
    // -----------------------------------------------------------------------

    public function test_supplier_bill_posts_journal_entry(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user);

        $service = app(SupplierBillService::class);

        $bill = $service->createBill([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'total'       => 250.00,
            'status'      => 'awaiting_payment',
        ]);

        $entry = JournalEntry::where('source_type', SupplierBill::class)
            ->where('source_id', $bill->id)
            ->first();

        $this->assertNotNull($entry, 'Journal entry should be posted for supplier bill.');
        $this->assertEquals('posted', $entry->status);

        // Dr Expenses, Cr AP
        $debitLine  = $entry->lines->firstWhere('debit', '>', 0);
        $creditLine = $entry->lines->firstWhere('credit', '>', 0);

        $this->assertNotNull($debitLine);
        $this->assertNotNull($creditLine);
        $this->assertEquals(250.00, (float) $debitLine->debit);
        $this->assertEquals(250.00, (float) $creditLine->credit);
    }

    public function test_accounting_service_posts_supplier_bill(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user);

        $bill = SupplierBill::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'total'       => 300.00,
            'status'      => 'awaiting_payment',
        ]);

        $accounting = app(AccountingService::class);
        $entry      = $accounting->postSupplierBillRecorded($bill);

        $this->assertNotNull($entry);
        $this->assertEquals(SupplierBill::class, $entry->source_type);
        $this->assertEquals($bill->id, $entry->source_id);
    }

    // -----------------------------------------------------------------------
    // Stage 5 — Payment reduces liability
    // -----------------------------------------------------------------------

    public function test_payment_reduces_supplier_bill_balance(): void
    {
        Event::fake([SupplierPaymentRecorded::class]);

        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user);

        $service = app(SupplierBillService::class);

        $bill = SupplierBill::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'total'       => 1000.00,
            'status'      => 'awaiting_payment',
        ]);

        $payment = $service->recordPayment($bill, [
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'amount'       => 400.00,
            'payment_date' => '2026-04-10',
        ]);

        $bill->refresh();

        $this->assertEquals(400.00, (float) $bill->amount_paid);
        $this->assertEquals(600.00, (float) $bill->balance);
        $this->assertEquals(SupplierBill::STATUS_PARTIAL, $bill->status);

        Event::assertDispatched(SupplierPaymentRecorded::class);
    }

    public function test_full_payment_marks_bill_paid(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user);

        $service = app(SupplierBillService::class);

        $bill = SupplierBill::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'total'       => 500.00,
            'status'      => 'awaiting_payment',
        ]);

        $service->recordPayment($bill, [
            'company_id'   => $this->companyId,
            'created_by'   => $user->id,
            'amount'       => 500.00,
            'payment_date' => '2026-04-12',
        ]);

        $bill->refresh();

        $this->assertEquals(SupplierBill::STATUS_PAID, $bill->status);
        $this->assertEquals(0.00, (float) $bill->balance);
    }

    public function test_payment_posts_ap_clearing_journal(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $this->actingAs($user);

        $bill = SupplierBill::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'total'       => 750.00,
            'status'      => 'awaiting_payment',
        ]);

        $accounting = app(AccountingService::class);
        $payment    = SupplierPayment::create([
            'company_id'       => $this->companyId,
            'created_by'       => $user->id,
            'supplier_bill_id' => $bill->id,
            'amount'           => 750.00,
            'payment_date'     => '2026-04-15',
        ]);

        $entry = $accounting->postSupplierPaymentRecorded($payment);

        $this->assertNotNull($entry);
        $this->assertEquals(SupplierPayment::class, $entry->source_type);

        // Dr AP, Cr Bank
        $debitLine  = $entry->lines->firstWhere('debit', '>', 0);
        $creditLine = $entry->lines->firstWhere('credit', '>', 0);

        $this->assertEquals(750.00, (float) $debitLine->debit);
        $this->assertEquals(750.00, (float) $creditLine->credit);
    }

    // -----------------------------------------------------------------------
    // Policy enforcement
    // -----------------------------------------------------------------------

    public function test_cannot_view_another_companys_bill(): void
    {
        $user     = $this->makeUser();
        $supplier = $this->makeSupplier();

        $otherUser = User::factory()->create(['company_id' => 999]);

        $bill = SupplierBill::create([
            'company_id'  => $this->companyId,
            'created_by'  => $user->id,
            'supplier_id' => $supplier->id,
            'bill_date'   => '2026-04-01',
            'total'       => 100.00,
            'status'      => 'draft',
        ]);

        $this->actingAs($otherUser)
            ->get(route('dashboard.money.supplier-bills.show', $bill))
            ->assertForbidden();
    }

    public function test_supplier_index_requires_auth(): void
    {
        $this->get(route('dashboard.money.suppliers.index'))
            ->assertRedirect(route('login'));
    }
}
