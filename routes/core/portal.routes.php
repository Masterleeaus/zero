<?php

// Loaded by RouteServiceProvider::loadCoreRoutes() under ['auth', 'throttle:120,1'] middleware.

use App\Http\Controllers\Core\Work\PortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal/service')->name('portal.service.')->group(function () {
    Route::get('/', [PortalController::class, 'index'])->name('index');
    Route::get('/jobs', [PortalController::class, 'jobs'])->name('jobs');
    Route::get('/jobs/{id}', [PortalController::class, 'showJob'])->name('jobs.show');
    Route::get('/invoices', [PortalController::class, 'invoices'])->name('invoices');
    Route::get('/quotes', [PortalController::class, 'quotes'])->name('quotes');
    Route::get('/agreements', [PortalController::class, 'agreements'])->name('agreements');
    Route::get('/assets', [PortalController::class, 'assets'])->name('assets');
    // fieldservice_sale_agreement portal exposure
    Route::get('/fsm-agreements/{agreement}', [PortalController::class, 'portalAgreementShow'])->name('fsm-agreements.show');
    Route::get('/fsm-agreements/{agreement}/invoices', [PortalController::class, 'portalAgreementInvoices'])->name('fsm-agreements.invoices');
    Route::get('/fsm-agreements/{agreement}/visits', [PortalController::class, 'portalAgreementVisits'])->name('fsm-agreements.visits');
});
