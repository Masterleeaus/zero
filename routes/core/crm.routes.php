<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('crm')->as('crm.')->group(static function () {
            Route::get('customers', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'index'])
                ->name('customers.index');
            Route::get('customers/{customer}', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'show'])
                ->name('customers.show');

            Route::get('enquiries', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'index'])
                ->name('enquiries.index');
            Route::get('enquiries/{enquiry}', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'show'])
                ->name('enquiries.show');
        });
    });
