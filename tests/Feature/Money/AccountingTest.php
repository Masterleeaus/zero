<?php

declare(strict_types=1);

namespace Tests\Feature\Money;

use App\Events\InvoiceIssued;
use App\Events\Money\ExpenseApproved;
use App\Events\Money\PaymentRecorded;
use App\Listeners\Money\PostExpenseApprovedToLedger;
use App\Listeners\Money\PostInvoiceIssuedToLedger;
use App\Listeners\Money\PostPaymentRecordedToLedger;
use App\Models\Money\Account;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JournalEntry;
use App\Models\Money\JournalLine;
use App\Models\Money\Payment;
use App\Models\User;
use App\Services\TitanMoney\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Chart of Accounts
    // -------------------------------------------------------------------------

    public function test_account_can_be_created(): void
    {
        $user = User::factory()->create(['company_id' => 10]);

        $this->actingAs($user)
            ->post(route('dashboard.money.accounts.store'), [
                'code'      => '1100',
                'name'      => 'Accounts Receivable',
                'type'      => 'asset',
            ])
            ->assertRedirect(route('dashboard.money.accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'company_id' => 10,
            'code'       => '1100',
            'name'       => 'Accounts Receivable',
            'type'       => 'asset',
        ]);
    }

    public function test_accounts_are_scoped_to_company(): void
    {
        $userA = User::factory()->create(['company_id' => 20]);
        $userB = User::factory()->create(['company_id' => 21]);

        Account::factory()->create(['company_id' => 20, 'name' => 'Company A Bank', 'type' => 'asset']);
        Account::factory()->create(['company_id' => 21, 'name' => 'Company B Bank', 'type' => 'asset']);

        $this->actingAs($userA)
            ->get(route('dashboard.money.accounts.index'))
            ->assertOk()
            ->assertSee('Company A Bank')
            ->assertDontSee('Company B Bank');
    }

    public function test_account_code_must_be_unique_per_company(): void
    {
        $user = User::factory()->create(['company_id' => 30]);
        Account::factory()->create(['company_id' => 30, 'code' => '1000', 'type' => 'asset', 'name' => 'Bank']);

        $this->actingAs($user)
            ->post(route('dashboard.money.accounts.store'), [
                'code' => '1000',
                'name' => 'Duplicate',
                'type' => 'asset',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_account_type_must_be_valid(): void
    {
        $user = User::factory()->create(['company_id' => 31]);

        $this->actingAs($user)
            ->post(route('dashboard.money.accounts.store'), [
                'name' => 'Bad Type',
                'type' => 'invalid_type',
            ])
            ->assertSessionHasErrors('type');
    }

    // -------------------------------------------------------------------------
    // AccountingService — createJournalEntry
    // -------------------------------------------------------------------------

    public function test_balanced_journal_entry_is_created(): void
    {
        $user    = User::factory()->create(['company_id' => 40]);
        $debitAcc  = Account::factory()->create(['company_id' => 40, 'type' => 'asset']);
        $creditAcc = Account::factory()->create(['company_id' => 40, 'type' => 'income']);

        $service = app(AccountingService::class);

        $entry = $service->createJournalEntry(
            companyId:   40,
            description: 'Test balanced entry',
            entryDate:   now()->toDateString(),
            lines: [
                ['account_id' => $debitAcc->id,  'debit' => 100.00, 'credit' => 0],
                ['account_id' => $creditAcc->id, 'debit' => 0,      'credit' => 100.00],
            ],
            createdBy: $user->id,
        );

        $this->assertInstanceOf(JournalEntry::class, $entry);
        $this->assertEquals(JournalEntry::STATUS_POSTED, $entry->status);
        $this->assertTrue($entry->isBalanced());
        $this->assertEquals(100.00, $entry->totalDebits());
        $this->assertEquals(100.00, $entry->totalCredits());
        $this->assertEquals(2, $entry->lines->count());
    }

    public function test_unbalanced_journal_entry_throws_exception(): void
    {
        $user      = User::factory()->create(['company_id' => 41]);
        $debitAcc  = Account::factory()->create(['company_id' => 41, 'type' => 'asset']);
        $creditAcc = Account::factory()->create(['company_id' => 41, 'type' => 'income']);

        $service = app(AccountingService::class);

        $this->expectException(InvalidArgumentException::class);

        $service->createJournalEntry(
            companyId:   41,
            description: 'Unbalanced entry',
            entryDate:   now()->toDateString(),
            lines: [
                ['account_id' => $debitAcc->id,  'debit' => 150.00, 'credit' => 0],
                ['account_id' => $creditAcc->id, 'debit' => 0,      'credit' => 100.00],
            ],
            createdBy: $user->id,
        );
    }

    // -------------------------------------------------------------------------
    // Auto-posting: Invoice Issued
    // -------------------------------------------------------------------------

    public function test_posting_invoice_issued_creates_journal_entry(): void
    {
        $user    = User::factory()->create(['company_id' => 50]);
        $invoice = Invoice::factory()->create([
            'company_id'     => 50,
            'total'          => 500.00,
            'issue_date'     => now()->toDateString(),
            'invoice_number' => 'INV-TEST-01',
        ]);

        $service = app(AccountingService::class);
        $entry   = $service->postInvoiceIssued($invoice);

        $this->assertNotNull($entry);
        $this->assertEquals(JournalEntry::STATUS_POSTED, $entry->status);
        $this->assertEquals(Invoice::class, $entry->source_type);
        $this->assertEquals($invoice->id, $entry->source_id);
        $this->assertTrue($entry->isBalanced());

        // Check that AR account was debited and income account was credited
        $lines = $entry->lines;
        $this->assertEquals(2, $lines->count());

        $debitLine  = $lines->where('debit', '>', 0)->first();
        $creditLine = $lines->where('credit', '>', 0)->first();

        $this->assertNotNull($debitLine);
        $this->assertNotNull($creditLine);
        $this->assertEquals(500.00, (float) $debitLine->debit);
        $this->assertEquals(500.00, (float) $creditLine->credit);
    }

    public function test_posting_invoice_is_idempotent(): void
    {
        $user    = User::factory()->create(['company_id' => 51]);
        $invoice = Invoice::factory()->create([
            'company_id' => 51,
            'total'      => 300.00,
        ]);

        $service = app(AccountingService::class);
        $service->postInvoiceIssued($invoice);
        $result = $service->postInvoiceIssued($invoice); // second call

        $this->assertNull($result);
        $this->assertEquals(1, JournalEntry::where('source_type', Invoice::class)
            ->where('source_id', $invoice->id)
            ->count());
    }

    // -------------------------------------------------------------------------
    // Auto-posting: Payment Recorded
    // -------------------------------------------------------------------------

    public function test_posting_payment_recorded_creates_journal_entry(): void
    {
        $user    = User::factory()->create(['company_id' => 60]);
        $invoice = Invoice::factory()->create(['company_id' => 60, 'total' => 200.00]);
        $payment = Payment::factory()->create([
            'company_id' => 60,
            'invoice_id' => $invoice->id,
            'amount'     => 200.00,
        ]);

        $service = app(AccountingService::class);
        $entry   = $service->postPaymentRecorded($payment);

        $this->assertNotNull($entry);
        $this->assertEquals(JournalEntry::STATUS_POSTED, $entry->status);
        $this->assertEquals(Payment::class, $entry->source_type);
        $this->assertEquals($payment->id, $entry->source_id);
        $this->assertTrue($entry->isBalanced());

        $lines = $entry->lines;
        $this->assertEquals(2, $lines->count());
    }

    // -------------------------------------------------------------------------
    // Auto-posting: Expense Approved
    // -------------------------------------------------------------------------

    public function test_posting_expense_approved_creates_journal_entry(): void
    {
        $user    = User::factory()->create(['company_id' => 70]);
        $expense = Expense::factory()->create([
            'company_id' => 70,
            'amount'     => 150.00,
            'status'     => 'approved',
        ]);

        $service = app(AccountingService::class);
        $entry   = $service->postExpenseApproved($expense);

        $this->assertNotNull($entry);
        $this->assertEquals(JournalEntry::STATUS_POSTED, $entry->status);
        $this->assertEquals(Expense::class, $entry->source_type);
        $this->assertEquals($expense->id, $entry->source_id);
        $this->assertTrue($entry->isBalanced());

        $lines = $entry->lines;
        $this->assertEquals(2, $lines->count());
    }

    // -------------------------------------------------------------------------
    // Event-listener wiring
    // -------------------------------------------------------------------------

    public function test_invoice_issued_event_registers_accounting_listener(): void
    {
        $listeners = Event::getListeners(InvoiceIssued::class);

        $listenerClasses = array_map(
            fn ($l) => is_string($l) ? $l : (is_array($l) ? $l[0] : get_class($l)),
            $listeners
        );

        $this->assertTrue(
            collect($listenerClasses)->contains(fn ($c) => str_contains($c, 'PostInvoiceIssuedToLedger')),
            'PostInvoiceIssuedToLedger must be registered for InvoiceIssued'
        );
    }

    public function test_payment_recorded_event_registers_accounting_listener(): void
    {
        $listeners = Event::getListeners(PaymentRecorded::class);

        $this->assertNotEmpty($listeners, 'PaymentRecorded must have at least one listener');
    }

    public function test_expense_approved_event_registers_accounting_listener(): void
    {
        $listeners = Event::getListeners(ExpenseApproved::class);

        $this->assertNotEmpty($listeners, 'ExpenseApproved must have at least one listener');
    }

    // -------------------------------------------------------------------------
    // Manual journal entry via UI
    // -------------------------------------------------------------------------

    public function test_manual_journal_entry_can_be_created_via_ui(): void
    {
        $user   = User::factory()->create(['company_id' => 80]);
        $accA   = Account::factory()->create(['company_id' => 80, 'type' => 'asset']);
        $accB   = Account::factory()->create(['company_id' => 80, 'type' => 'income']);

        $this->actingAs($user)
            ->post(route('dashboard.money.journal.store'), [
                'description' => 'Manual test entry',
                'entry_date'  => now()->toDateString(),
                'lines' => [
                    ['account_id' => $accA->id, 'debit' => '250.00', 'credit' => '0.00'],
                    ['account_id' => $accB->id, 'debit' => '0.00',   'credit' => '250.00'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('journal_entries', [
            'company_id'  => 80,
            'description' => 'Manual test entry',
            'status'      => JournalEntry::STATUS_POSTED,
        ]);

        $this->assertEquals(2, JournalLine::whereHas('journalEntry', fn ($q) => $q->where('company_id', 80))->count());
    }

    public function test_unbalanced_manual_journal_entry_is_rejected(): void
    {
        $user = User::factory()->create(['company_id' => 81]);
        $accA = Account::factory()->create(['company_id' => 81, 'type' => 'asset']);
        $accB = Account::factory()->create(['company_id' => 81, 'type' => 'income']);

        $this->actingAs($user)
            ->post(route('dashboard.money.journal.store'), [
                'description' => 'Unbalanced entry',
                'entry_date'  => now()->toDateString(),
                'lines' => [
                    ['account_id' => $accA->id, 'debit' => '999.00', 'credit' => '0.00'],
                    ['account_id' => $accB->id, 'debit' => '0.00',   'credit' => '500.00'],
                ],
            ])
            ->assertSessionHasErrors('lines');

        $this->assertDatabaseMissing('journal_entries', ['company_id' => 81]);
    }

    // -------------------------------------------------------------------------
    // Tenancy isolation
    // -------------------------------------------------------------------------

    public function test_journal_entries_are_scoped_to_company(): void
    {
        $userA = User::factory()->create(['company_id' => 90]);
        $userB = User::factory()->create(['company_id' => 91]);

        $accA = Account::factory()->create(['company_id' => 90, 'type' => 'asset']);
        $accB = Account::factory()->create(['company_id' => 91, 'type' => 'asset']);
        $incA = Account::factory()->create(['company_id' => 90, 'type' => 'income']);
        $incB = Account::factory()->create(['company_id' => 91, 'type' => 'income']);

        $service = app(AccountingService::class);

        $this->actingAs($userA);
        $service->createJournalEntry(90, 'Company A entry', now()->toDateString(), [
            ['account_id' => $accA->id, 'debit' => 100, 'credit' => 0],
            ['account_id' => $incA->id, 'debit' => 0, 'credit' => 100],
        ], createdBy: $userA->id);

        $this->actingAs($userB);
        $this->get(route('dashboard.money.journal.index'))
            ->assertOk()
            ->assertDontSee('Company A entry');
    }
}
