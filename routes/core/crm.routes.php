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

            Route::get('deals', [\App\Http\Controllers\Core\Crm\DealController::class, 'index'])
                ->name('deals.index');
            Route::get('deals/create', [\App\Http\Controllers\Core\Crm\DealController::class, 'create'])
                ->name('deals.create');
            Route::post('deals', [\App\Http\Controllers\Core\Crm\DealController::class, 'store'])
                ->name('deals.store');
            Route::get('deals/kanban', [\App\Http\Controllers\Core\Crm\DealController::class, 'kanban'])
                ->name('deals.kanban');
            Route::get('deals/{deal}', [\App\Http\Controllers\Core\Crm\DealController::class, 'show'])
                ->name('deals.show');
            Route::get('deals/{deal}/edit', [\App\Http\Controllers\Core\Crm\DealController::class, 'edit'])
                ->name('deals.edit');
            Route::put('deals/{deal}', [\App\Http\Controllers\Core\Crm\DealController::class, 'update'])
                ->name('deals.update');
            Route::delete('deals/{deal}', [\App\Http\Controllers\Core\Crm\DealController::class, 'destroy'])
                ->name('deals.destroy');
            Route::post('deals/{deal}/status', [\App\Http\Controllers\Core\Crm\DealController::class, 'updateStatus'])
                ->name('deals.status');

            Route::get('deals/{deal}/notes', [\App\Http\Controllers\Core\Crm\DealNoteController::class, 'index'])
                ->name('deals.notes.index');
            Route::post('deals/{deal}/notes', [\App\Http\Controllers\Core\Crm\DealNoteController::class, 'store'])
                ->name('deals.notes.store');
            Route::put('deals/{deal}/notes/{note}', [\App\Http\Controllers\Core\Crm\DealNoteController::class, 'update'])
                ->name('deals.notes.update');
            Route::delete('deals/{deal}/notes/{note}', [\App\Http\Controllers\Core\Crm\DealNoteController::class, 'destroy'])
                ->name('deals.notes.destroy');
        });
    });
