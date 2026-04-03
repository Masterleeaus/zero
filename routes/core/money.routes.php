<?php

use Illuminate\Support\Facades\Route;

$dashboardThrottleMiddleware = config('app.dashboard_throttle_middleware', 'throttle:120,1');

Route::middleware(['auth', 'updateUserActivity', $dashboardThrottleMiddleware])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('money')->as('money.')->group(static function () {
            Route::get('quotes', [\App\Http\Controllers\Core\Money\QuoteController::class, 'index'])
                ->name('quotes.index');
            Route::get('quotes/create', [\App\Http\Controllers\Core\Money\QuoteController::class, 'create'])
                ->name('quotes.create');
            Route::post('quotes', [\App\Http\Controllers\Core\Money\QuoteController::class, 'store'])
                ->name('quotes.store');
            Route::get('quotes/{quote}', [\App\Http\Controllers\Core\Money\QuoteController::class, 'show'])
                ->name('quotes.show');
            Route::get('quotes/{quote}/edit', [\App\Http\Controllers\Core\Money\QuoteController::class, 'edit'])
                ->name('quotes.edit');
            Route::put('quotes/{quote}', [\App\Http\Controllers\Core\Money\QuoteController::class, 'update'])
                ->name('quotes.update');
            Route::post('quotes/{quote}/status', [\App\Http\Controllers\Core\Money\QuoteController::class, 'updateStatus'])
                ->name('quotes.status');
            Route::post('quotes/{quote}/convert-job', [\App\Http\Controllers\Core\Money\QuoteController::class, 'convertToJob'])
                ->name('quotes.convert-job');

            Route::get('invoices', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'index'])
                ->name('invoices.index');
            Route::get('invoices/create', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'create'])
                ->name('invoices.create');
            Route::post('invoices', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'store'])
                ->name('invoices.store');
            Route::get('invoices/{invoice}', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'show'])
                ->name('invoices.show');
            Route::get('invoices/{invoice}/edit', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'edit'])
                ->name('invoices.edit');
            Route::put('invoices/{invoice}', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'update'])
                ->name('invoices.update');
            Route::post('invoices/{invoice}/mark-paid', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'markPaid'])
                ->name('invoices.mark-paid');
            Route::post('invoices/{invoice}/mark-overdue', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'markOverdue'])
                ->name('invoices.mark-overdue');

            Route::post('invoices/{invoice}/payments', [\App\Http\Controllers\Core\Money\PaymentController::class, 'store'])
                ->name('payments.store');
            Route::get('payments', [\App\Http\Controllers\Core\Money\PaymentController::class, 'index'])
                ->name('payments.index');
            Route::post('quotes/{quote}/create-invoice', [\App\Http\Controllers\Core\Money\QuoteController::class, 'convertToInvoice'])
                ->name('quotes.convert-invoice');

            Route::get('quote-templates', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'index'])
                ->name('quote-templates.index');
            Route::get('quote-templates/create', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'create'])
                ->name('quote-templates.create');
            Route::post('quote-templates', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'store'])
                ->name('quote-templates.store');
            Route::get('quote-templates/{template}', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'show'])
                ->name('quote-templates.show');
            Route::get('quote-templates/{template}/edit', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'edit'])
                ->name('quote-templates.edit');
            Route::put('quote-templates/{template}', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'update'])
                ->name('quote-templates.update');
            Route::delete('quote-templates/{template}', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'destroy'])
                ->name('quote-templates.destroy');
            Route::post('quote-templates/{template}/apply', [\App\Http\Controllers\Core\Money\QuoteTemplateController::class, 'applyToQuote'])
                ->name('quote-templates.apply');

            Route::get('expenses', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'index'])
                ->name('expenses.index');
            Route::get('expenses/create', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'create'])
                ->name('expenses.create');
            Route::post('expenses', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'store'])
                ->name('expenses.store');
            Route::get('expenses/{expense}', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'show'])
                ->name('expenses.show');
            Route::get('expenses/{expense}/edit', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'edit'])
                ->name('expenses.edit');
            Route::put('expenses/{expense}', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'update'])
                ->name('expenses.update');
            Route::delete('expenses/{expense}', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'destroy'])
                ->name('expenses.destroy');
            Route::post('expenses/{expense}/approve', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'approve'])
                ->name('expenses.approve');
            Route::post('expenses/{expense}/reject', [\App\Http\Controllers\Core\Money\ExpenseController::class, 'reject'])
                ->name('expenses.reject');

            Route::get('credit-notes', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'index'])
                ->name('credit-notes.index');
            Route::get('credit-notes/create', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'create'])
                ->name('credit-notes.create');
            Route::post('credit-notes', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'store'])
                ->name('credit-notes.store');
            Route::get('credit-notes/{creditNote}', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'show'])
                ->name('credit-notes.show');
            Route::get('credit-notes/{creditNote}/edit', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'edit'])
                ->name('credit-notes.edit');
            Route::put('credit-notes/{creditNote}', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'update'])
                ->name('credit-notes.update');
            Route::delete('credit-notes/{creditNote}', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'destroy'])
                ->name('credit-notes.destroy');
            Route::post('credit-notes/{creditNote}/apply-invoice', [\App\Http\Controllers\Core\Money\CreditNoteController::class, 'applyToInvoice'])
                ->name('credit-notes.apply-invoice');

            Route::get('bank-accounts', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'index'])
                ->name('bank-accounts.index');
            Route::get('bank-accounts/create', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'create'])
                ->name('bank-accounts.create');
            Route::post('bank-accounts', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'store'])
                ->name('bank-accounts.store');
            Route::get('bank-accounts/{account}', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'show'])
                ->name('bank-accounts.show');
            Route::get('bank-accounts/{account}/edit', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'edit'])
                ->name('bank-accounts.edit');
            Route::put('bank-accounts/{account}', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'update'])
                ->name('bank-accounts.update');
            Route::delete('bank-accounts/{account}', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'destroy'])
                ->name('bank-accounts.destroy');
            Route::post('bank-accounts/{account}/default', [\App\Http\Controllers\Core\Money\BankAccountController::class, 'setDefault'])
                ->name('bank-accounts.set-default');

            Route::get('taxes', [\App\Http\Controllers\Core\Money\TaxController::class, 'index'])
                ->name('taxes.index');
            Route::get('taxes/create', [\App\Http\Controllers\Core\Money\TaxController::class, 'create'])
                ->name('taxes.create');
            Route::post('taxes', [\App\Http\Controllers\Core\Money\TaxController::class, 'store'])
                ->name('taxes.store');
            Route::get('taxes/{tax}', [\App\Http\Controllers\Core\Money\TaxController::class, 'show'])
                ->name('taxes.show');
            Route::get('taxes/{tax}/edit', [\App\Http\Controllers\Core\Money\TaxController::class, 'edit'])
                ->name('taxes.edit');
            Route::put('taxes/{tax}', [\App\Http\Controllers\Core\Money\TaxController::class, 'update'])
                ->name('taxes.update');
            Route::delete('taxes/{tax}', [\App\Http\Controllers\Core\Money\TaxController::class, 'destroy'])
                ->name('taxes.destroy');
            Route::post('taxes/{tax}/default', [\App\Http\Controllers\Core\Money\TaxController::class, 'setDefault'])
                ->name('taxes.set-default');

            Route::get('expense-categories', [\App\Http\Controllers\Core\Money\ExpenseCategoryController::class, 'index'])
                ->name('expense-categories.index');
            Route::get('expense-categories/create', [\App\Http\Controllers\Core\Money\ExpenseCategoryController::class, 'create'])
                ->name('expense-categories.create');
            Route::post('expense-categories', [\App\Http\Controllers\Core\Money\ExpenseCategoryController::class, 'store'])
                ->name('expense-categories.store');
            Route::get('expense-categories/{expenseCategory}/edit', [\App\Http\Controllers\Core\Money\ExpenseCategoryController::class, 'edit'])
                ->name('expense-categories.edit');
            Route::put('expense-categories/{expenseCategory}', [\App\Http\Controllers\Core\Money\ExpenseCategoryController::class, 'update'])
                ->name('expense-categories.update');
            Route::delete('expense-categories/{expenseCategory}', [\App\Http\Controllers\Core\Money\ExpenseCategoryController::class, 'destroy'])
                ->name('expense-categories.destroy');

            // ── Chart of Accounts ─────────────────────────────────────────────
            Route::get('accounts', [\App\Http\Controllers\Core\Money\AccountController::class, 'index'])
                ->name('accounts.index');
            Route::get('accounts/create', [\App\Http\Controllers\Core\Money\AccountController::class, 'create'])
                ->name('accounts.create');
            Route::post('accounts', [\App\Http\Controllers\Core\Money\AccountController::class, 'store'])
                ->name('accounts.store');
            Route::get('accounts/{account}', [\App\Http\Controllers\Core\Money\AccountController::class, 'show'])
                ->name('accounts.show');
            Route::get('accounts/{account}/edit', [\App\Http\Controllers\Core\Money\AccountController::class, 'edit'])
                ->name('accounts.edit');
            Route::put('accounts/{account}', [\App\Http\Controllers\Core\Money\AccountController::class, 'update'])
                ->name('accounts.update');
            Route::delete('accounts/{account}', [\App\Http\Controllers\Core\Money\AccountController::class, 'destroy'])
                ->name('accounts.destroy');

            // ── Journal Entries ────────────────────────────────────────────────
            Route::get('journal', [\App\Http\Controllers\Core\Money\JournalEntryController::class, 'index'])
                ->name('journal.index');
            Route::get('journal/create', [\App\Http\Controllers\Core\Money\JournalEntryController::class, 'create'])
                ->name('journal.create');
            Route::post('journal', [\App\Http\Controllers\Core\Money\JournalEntryController::class, 'store'])
                ->name('journal.store');
            Route::get('journal/{journalEntry}', [\App\Http\Controllers\Core\Money\JournalEntryController::class, 'show'])
                ->name('journal.show');

            // ── Suppliers (AP registry) ────────────────────────────────────────
            Route::get('suppliers', [\App\Http\Controllers\Core\Money\SupplierController::class, 'index'])
                ->name('suppliers.index');
            Route::get('suppliers/create', [\App\Http\Controllers\Core\Money\SupplierController::class, 'create'])
                ->name('suppliers.create');
            Route::post('suppliers', [\App\Http\Controllers\Core\Money\SupplierController::class, 'store'])
                ->name('suppliers.store');
            Route::get('suppliers/{supplier}', [\App\Http\Controllers\Core\Money\SupplierController::class, 'show'])
                ->name('suppliers.show');
            Route::get('suppliers/{supplier}/edit', [\App\Http\Controllers\Core\Money\SupplierController::class, 'edit'])
                ->name('suppliers.edit');
            Route::put('suppliers/{supplier}', [\App\Http\Controllers\Core\Money\SupplierController::class, 'update'])
                ->name('suppliers.update');
            Route::delete('suppliers/{supplier}', [\App\Http\Controllers\Core\Money\SupplierController::class, 'destroy'])
                ->name('suppliers.destroy');

            // ── Purchase Orders (AP context) ───────────────────────────────────
            Route::get('purchase-orders', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'index'])
                ->name('purchase-orders.index');
            Route::get('purchase-orders/create', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'create'])
                ->name('purchase-orders.create');
            Route::post('purchase-orders', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'store'])
                ->name('purchase-orders.store');
            Route::get('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'show'])
                ->name('purchase-orders.show');
            Route::get('purchase-orders/{purchaseOrder}/edit', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'edit'])
                ->name('purchase-orders.edit');
            Route::put('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'update'])
                ->name('purchase-orders.update');
            Route::delete('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\Core\Money\PurchaseOrderController::class, 'destroy'])
                ->name('purchase-orders.destroy');

            // ── Supplier Bills (Accounts Payable) ──────────────────────────────
            Route::get('supplier-bills', [\App\Http\Controllers\Core\Money\SupplierBillController::class, 'index'])
                ->name('supplier-bills.index');
            Route::get('supplier-bills/create', [\App\Http\Controllers\Core\Money\SupplierBillController::class, 'create'])
                ->name('supplier-bills.create');
            Route::post('supplier-bills', [\App\Http\Controllers\Core\Money\SupplierBillController::class, 'store'])
                ->name('supplier-bills.store');
            Route::get('supplier-bills/{supplierBill}', [\App\Http\Controllers\Core\Money\SupplierBillController::class, 'show'])
                ->name('supplier-bills.show');
            Route::get('supplier-bills/{supplierBill}/edit', [\App\Http\Controllers\Core\Money\SupplierBillController::class, 'edit'])
                ->name('supplier-bills.edit');
            Route::put('supplier-bills/{supplierBill}', [\App\Http\Controllers\Core\Money\SupplierBillController::class, 'update'])
                ->name('supplier-bills.update');
            Route::post('supplier-bills/{supplierBill}/payments', [\App\Http\Controllers\Core\Money\SupplierPaymentController::class, 'store'])
                ->name('supplier-payments.store');
        });
    });
