<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
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
        });
    });
