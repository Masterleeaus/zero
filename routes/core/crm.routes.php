<?php

use Illuminate\Support\Facades\Route;

const DASHBOARD_CRM_THROTTLE_LIMIT = 'throttle:120,1';

Route::middleware(['auth', 'updateUserActivity', DASHBOARD_CRM_THROTTLE_LIMIT])
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
            Route::get('customers/{customer}/contacts', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'index'])
                ->name('customers.contacts.index');
            Route::get('customers/{customer}/contacts/create', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'create'])
                ->name('customers.contacts.create');
            Route::post('customers/{customer}/contacts', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'store'])
                ->name('customers.contacts.store');
            Route::get('customers/{customer}/contacts/{contact}', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'show'])
                ->name('customers.contacts.show');
            Route::get('customers/{customer}/contacts/{contact}/edit', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'edit'])
                ->name('customers.contacts.edit');
            Route::put('customers/{customer}/contacts/{contact}', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'update'])
                ->name('customers.contacts.update');
            Route::delete('customers/{customer}/contacts/{contact}', [\App\Http\Controllers\Core\Crm\CustomerContactController::class, 'destroy'])
                ->name('customers.contacts.destroy');

            Route::get('customers/{customer}/notes', [\App\Http\Controllers\Core\Crm\CustomerNoteController::class, 'index'])
                ->name('customers.notes.index');
            Route::post('customers/{customer}/notes', [\App\Http\Controllers\Core\Crm\CustomerNoteController::class, 'store'])
                ->name('customers.notes.store');
            Route::put('customers/{customer}/notes/{note}', [\App\Http\Controllers\Core\Crm\CustomerNoteController::class, 'update'])
                ->name('customers.notes.update');
            Route::delete('customers/{customer}/notes/{note}', [\App\Http\Controllers\Core\Crm\CustomerNoteController::class, 'destroy'])
                ->name('customers.notes.destroy');
            Route::post('customers/{customer}/notes/{note}/pin', [\App\Http\Controllers\Core\Crm\CustomerNoteController::class, 'togglePin'])
                ->name('customers.notes.pin');

            Route::get('customers/{customer}/documents', [\App\Http\Controllers\Core\Crm\CustomerDocumentController::class, 'index'])
                ->name('customers.documents.index');
            Route::post('customers/{customer}/documents', [\App\Http\Controllers\Core\Crm\CustomerDocumentController::class, 'store'])
                ->name('customers.documents.store');
            Route::get('customers/{customer}/documents/{document}/download', [\App\Http\Controllers\Core\Crm\CustomerDocumentController::class, 'download'])
                ->name('customers.documents.download');
            Route::delete('customers/{customer}/documents/{document}', [\App\Http\Controllers\Core\Crm\CustomerDocumentController::class, 'destroy'])
                ->name('customers.documents.destroy');

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
            Route::post('enquiries/{enquiry}/convert-to-job', [\App\Http\Controllers\Core\Crm\EnquiryController::class, 'convertToServiceJob'])
                ->name('enquiries.convert-to-job');
        });
    });
