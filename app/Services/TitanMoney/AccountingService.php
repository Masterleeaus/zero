<?php

declare(strict_types=1);

namespace App\Services\TitanMoney;

use App\Models\Money\Account;
use App\Models\Money\Expense;
use App\Models\Money\Invoice;
use App\Models\Money\JournalEntry;
use App\Models\Money\JournalLine;
use App\Models\Money\Payment;
use App\Models\Money\SupplierBill;
use App\Models\Money\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * AccountingService — double-entry posting backbone for TitanMoney.
 *
 * Responsibilities:
 *   - Create balanced journal entries
 *   - Post entries for invoice issuance, payment recording, expense approval
 *   - Enforce debit/credit balance (throw on imbalance)
 *   - Provide reusable posting helpers for future phases (purchases, payroll, assets)
 *
 * Each posting method is idempotent-safe: it checks whether a journal entry
 * already exists for the given source before creating a new one.
 */
class AccountingService
{
    // ------------------------------------------------------------------
    // Core API
    // ------------------------------------------------------------------

    /**
     * Create a balanced journal entry with one or more debit/credit line pairs.
     *
     * $lines format:
     * [
     *   ['account_id' => 1, 'debit' => 100.00, 'credit' => 0, 'description' => '…'],
     *   ['account_id' => 2, 'debit' => 0, 'credit' => 100.00, 'description' => '…'],
     * ]
     *
     * @throws \InvalidArgumentException when the entry is not balanced.
     */
    public function createJournalEntry(
        int $companyId,
        string $description,
        string $entryDate,
        array $lines,
        ?string $reference = null,
        ?string $currency = 'AUD',
        ?string $sourceType = null,
        ?int $sourceId = null,
        int $createdBy = 0,
    ): JournalEntry {
        $this->assertBalanced($lines);

        return DB::transaction(function () use (
            $companyId, $description, $entryDate, $lines,
            $reference, $currency, $sourceType, $sourceId, $createdBy
        ): JournalEntry {
            $entry = JournalEntry::create([
                'company_id'  => $companyId,
                'created_by'  => $createdBy ?: null,
                'reference'   => $reference,
                'description' => $description,
                'entry_date'  => $entryDate,
                'status'      => JournalEntry::STATUS_POSTED,
                'currency'    => $currency ?? 'AUD',
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
            ]);

            foreach ($lines as $line) {
                JournalLine::create([
                    'company_id'       => $companyId,
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'description'      => $line['description'] ?? null,
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                ]);
            }

            return $entry->load('lines');
        });
    }

    // ------------------------------------------------------------------
    // Domain posting helpers
    // ------------------------------------------------------------------

    /**
     * Post an invoice-issued journal entry.
     *
     * Debit:  Accounts Receivable  (type=asset)
     * Credit: Sales / Income       (type=income)
     *
     * Safe to call multiple times — skips if already posted.
     */
    public function postInvoiceIssued(Invoice $invoice): ?JournalEntry
    {
        if ($this->alreadyPosted(Invoice::class, $invoice->id)) {
            return null;
        }

        $companyId = $invoice->company_id;
        $ar        = $this->findOrCreateAccount($companyId, 'Accounts Receivable', 'asset', '1200');
        $income    = $this->findOrCreateAccount($companyId, 'Sales Income', 'income', '4000');

        if (! $ar || ! $income) {
            Log::warning('AccountingService: cannot post invoice — accounts unavailable', [
                'invoice_id' => $invoice->id,
            ]);
            return null;
        }

        $amount = (float) $invoice->total;

        try {
            return $this->createJournalEntry(
                companyId:   $companyId,
                description: 'Invoice issued: ' . $invoice->invoice_number,
                entryDate:   $invoice->issue_date?->toDateString() ?? now()->toDateString(),
                lines: [
                    ['account_id' => $ar->id,     'debit'  => $amount, 'credit' => 0,      'description' => 'AR — ' . $invoice->invoice_number],
                    ['account_id' => $income->id,  'debit'  => 0,       'credit' => $amount, 'description' => 'Revenue — ' . $invoice->invoice_number],
                ],
                reference:   $invoice->invoice_number,
                currency:    $invoice->currency ?? 'AUD',
                sourceType:  Invoice::class,
                sourceId:    $invoice->id,
                createdBy:   (int) $invoice->created_by,
            );
        } catch (\Throwable $e) {
            Log::error('AccountingService: postInvoiceIssued failed', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Post a payment-recorded journal entry.
     *
     * Debit:  Bank / Cash            (type=asset)
     * Credit: Accounts Receivable    (type=asset)
     *
     * Safe to call multiple times — skips if already posted.
     */
    public function postPaymentRecorded(Payment $payment): ?JournalEntry
    {
        if ($this->alreadyPosted(Payment::class, $payment->id)) {
            return null;
        }

        $companyId = $payment->company_id;
        $bank      = $this->findOrCreateAccount($companyId, 'Bank', 'asset', '1000');
        $ar        = $this->findOrCreateAccount($companyId, 'Accounts Receivable', 'asset', '1200');

        if (! $bank || ! $ar) {
            Log::warning('AccountingService: cannot post payment — accounts unavailable', [
                'payment_id' => $payment->id,
            ]);
            return null;
        }

        $amount = (float) $payment->amount;
        $ref    = $payment->reference ?? ('PAY-' . $payment->id);

        try {
            return $this->createJournalEntry(
                companyId:   $companyId,
                description: 'Payment received: ' . $ref,
                entryDate:   $payment->paid_at?->toDateString() ?? now()->toDateString(),
                lines: [
                    ['account_id' => $bank->id, 'debit'  => $amount, 'credit' => 0,      'description' => 'Bank receipt — ' . $ref],
                    ['account_id' => $ar->id,   'debit'  => 0,       'credit' => $amount, 'description' => 'AR cleared — ' . $ref],
                ],
                reference:   $ref,
                currency:    'AUD',
                sourceType:  Payment::class,
                sourceId:    $payment->id,
                createdBy:   (int) $payment->created_by,
            );
        } catch (\Throwable $e) {
            Log::error('AccountingService: postPaymentRecorded failed', [
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Post a supplier-bill journal entry.
     *
     * Debit:  Operating Expenses   (type=expense)
     * Credit: Accounts Payable     (type=liability)
     *
     * Safe to call multiple times — skips if already posted.
     */
    public function postSupplierBillRecorded(SupplierBill $bill): ?JournalEntry
    {
        if ($this->alreadyPosted(SupplierBill::class, $bill->id)) {
            return null;
        }

        $companyId = $bill->company_id;
        $expAcc    = $this->findOrCreateAccount($companyId, 'Operating Expenses', 'expense', '6000');
        $payable   = $this->findOrCreateAccount($companyId, 'Accounts Payable', 'liability', '2000');

        if (! $expAcc || ! $payable) {
            Log::warning('AccountingService: cannot post supplier bill — accounts unavailable', [
                'supplier_bill_id' => $bill->id,
            ]);
            return null;
        }

        $amount = (float) $bill->total;
        $ref    = $bill->reference ?? ('BILL-' . $bill->id);

        try {
            return $this->createJournalEntry(
                companyId:   $companyId,
                description: 'Supplier bill recorded: ' . $ref,
                entryDate:   $bill->bill_date?->toDateString() ?? now()->toDateString(),
                lines: [
                    ['account_id' => $expAcc->id, 'debit'  => $amount, 'credit' => 0,      'description' => 'Expense — ' . $ref],
                    ['account_id' => $payable->id, 'debit'  => 0,       'credit' => $amount, 'description' => 'AP — ' . $ref],
                ],
                reference:   $ref,
                currency:    $bill->currency ?? 'AUD',
                sourceType:  SupplierBill::class,
                sourceId:    $bill->id,
                createdBy:   (int) $bill->created_by,
            );
        } catch (\Throwable $e) {
            Log::error('AccountingService: postSupplierBillRecorded failed', [
                'supplier_bill_id' => $bill->id,
                'error'            => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Post a supplier-payment journal entry.
     *
     * Debit:  Accounts Payable   (type=liability)
     * Credit: Bank / Cash        (type=asset)
     *
     * Safe to call multiple times — skips if already posted.
     */
    public function postSupplierPaymentRecorded(SupplierPayment $payment): ?JournalEntry
    {
        if ($this->alreadyPosted(SupplierPayment::class, $payment->id)) {
            return null;
        }

        $companyId = $payment->company_id;
        $payable   = $this->findOrCreateAccount($companyId, 'Accounts Payable', 'liability', '2000');
        $bank      = $this->findOrCreateAccount($companyId, 'Bank', 'asset', '1000');

        if (! $payable || ! $bank) {
            Log::warning('AccountingService: cannot post supplier payment — accounts unavailable', [
                'supplier_payment_id' => $payment->id,
            ]);
            return null;
        }

        $amount = (float) $payment->amount;
        $ref    = $payment->reference ?? ('SPAY-' . $payment->id);

        try {
            return $this->createJournalEntry(
                companyId:   $companyId,
                description: 'Supplier payment: ' . $ref,
                entryDate:   $payment->payment_date?->toDateString() ?? now()->toDateString(),
                lines: [
                    ['account_id' => $payable->id, 'debit'  => $amount, 'credit' => 0,      'description' => 'AP cleared — ' . $ref],
                    ['account_id' => $bank->id,    'debit'  => 0,       'credit' => $amount, 'description' => 'Bank — ' . $ref],
                ],
                reference:   $ref,
                currency:    'AUD',
                sourceType:  SupplierPayment::class,
                sourceId:    $payment->id,
                createdBy:   (int) $payment->created_by,
            );
        } catch (\Throwable $e) {
            Log::error('AccountingService: postSupplierPaymentRecorded failed', [
                'supplier_payment_id' => $payment->id,
                'error'               => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Post an expense-approved journal entry.
     *
     * Debit:  Operating Expenses   (type=expense)
     * Credit: Accounts Payable     (type=liability)
     *
     * Safe to call multiple times — skips if already posted.
     */
    public function postExpenseApproved(Expense $expense): ?JournalEntry
    {
        if ($this->alreadyPosted(Expense::class, $expense->id)) {
            return null;
        }

        $companyId = $expense->company_id;
        $expAcc    = $this->findOrCreateAccount($companyId, 'Operating Expenses', 'expense', '6000');
        $payable   = $this->findOrCreateAccount($companyId, 'Accounts Payable', 'liability', '2000');

        if (! $expAcc || ! $payable) {
            Log::warning('AccountingService: cannot post expense — accounts unavailable', [
                'expense_id' => $expense->id,
            ]);
            return null;
        }

        $amount = (float) $expense->amount;
        $ref    = 'EXP-' . $expense->id;

        try {
            return $this->createJournalEntry(
                companyId:   $companyId,
                description: 'Expense approved: ' . ($expense->title ?? $ref),
                entryDate:   $expense->expense_date?->toDateString() ?? now()->toDateString(),
                lines: [
                    ['account_id' => $expAcc->id,  'debit'  => $amount, 'credit' => 0,      'description' => 'Expense — ' . ($expense->title ?? $ref)],
                    ['account_id' => $payable->id, 'debit'  => 0,       'credit' => $amount, 'description' => 'Payable — ' . ($expense->title ?? $ref)],
                ],
                reference:   $ref,
                currency:    'AUD',
                sourceType:  Expense::class,
                sourceId:    $expense->id,
                createdBy:   (int) $expense->created_by,
            );
        } catch (\Throwable $e) {
            Log::error('AccountingService: postExpenseApproved failed', [
                'expense_id' => $expense->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * Assert that the given lines are balanced (total debits === total credits).
     *
     * @throws \InvalidArgumentException
     */
    private function assertBalanced(array $lines): void
    {
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        if (abs($totalDebit - $totalCredit) >= 0.001) {
            throw new InvalidArgumentException(
                sprintf(
                    'Journal entry is not balanced: debits %.2f ≠ credits %.2f',
                    $totalDebit,
                    $totalCredit
                )
            );
        }
    }

    /**
     * Check whether a journal entry already exists for a given source document.
     */
    private function alreadyPosted(string $sourceType, int $sourceId): bool
    {
        return JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('status', JournalEntry::STATUS_POSTED)
            ->exists();
    }

    /**
     * Find an account by code within the company, or create a default one.
     */
    private function findOrCreateAccount(
        int $companyId,
        string $name,
        string $type,
        string $code,
    ): ?Account {
        return Account::firstOrCreate(
            ['company_id' => $companyId, 'code' => $code],
            [
                'name'       => $name,
                'type'       => $type,
                'is_active'  => true,
            ]
        );
    }
}
