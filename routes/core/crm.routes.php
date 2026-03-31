<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'updateUserActivity'])
    ->prefix('dashboard')
    ->as('dashboard.')
    ->group(static function () {
        Route::prefix('crm')->as('crm.')->group(static function () {
            Route::get('customers', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'index'])
                ->name('customers.index');
            Route::get('customers/create', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'create'])
                ->name('customers.create');
            Route::post('customers', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'store'])
                ->name('customers.store');
            Route::get('customers/{customer}', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'show'])
                ->name('customers.show');
            Route::get('customers/{customer}/edit', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'edit'])
                ->name('customers.edit');
            Route::put('customers/{customer}', [\App\Http\Controllers\Core\Crm\CustomerController::class, 'update'])
                ->name('customers.update');

            Route::get('enquiries', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'index'])
                ->name('enquiries.index');
            Route::get('enquiries/create', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'create'])
                ->name('enquiries.create');
            Route::post('enquiries', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'store'])
                ->name('enquiries.store');
            Route::get('enquiries/{enquiry}', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'show'])
                ->name('enquiries.show');
            Route::put('enquiries/{enquiry}', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'update'])
                ->name('enquiries.update');
            Route::post('enquiries/{enquiry}/convert-to-quote', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'convertToQuote'])
                ->name('enquiries.convert-to-quote');
        });
    });
