<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        // TODO: migrate WorkCore money routes (quotes/invoices/payments/expenses) here.
    });
