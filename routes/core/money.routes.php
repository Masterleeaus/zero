<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('money')->as('money.')->group(static function () {
            Route::get('quotes', [\App\Http\Controllers\Core\Money\QuoteController::class, 'index'])
                ->name('quotes.index');
            Route::get('quotes/{quote}', [\App\Http\Controllers\Core\Money\QuoteController::class, 'show'])
                ->name('quotes.show');

            Route::get('invoices', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'index'])
                ->name('invoices.index');
            Route::get('invoices/{invoice}', [\App\Http\Controllers\Core\Money\InvoiceController::class, 'show'])
                ->name('invoices.show');
        });
    });
