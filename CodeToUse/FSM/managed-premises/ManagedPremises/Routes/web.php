<?php

use Illuminate\Support\Facades\Route;

use Modules\ManagedPremises\Http\Controllers\PropertiesController;
use Modules\ManagedPremises\Http\Controllers\PropertyOverviewController;
use Modules\ManagedPremises\Http\Controllers\PropertyUnitsController;
use Modules\ManagedPremises\Http\Controllers\PropertyContactsController;
use Modules\ManagedPremises\Http\Controllers\PropertyJobsController;
use Modules\ManagedPremises\Http\Controllers\PropertyKeysController;
use Modules\ManagedPremises\Http\Controllers\PropertyPhotosController;
use Modules\ManagedPremises\Http\Controllers\PropertyChecklistsController;
use Modules\ManagedPremises\Http\Controllers\PropertyTagsController;
use Modules\ManagedPremises\Http\Controllers\PropertyRoomsController;
use Modules\ManagedPremises\Http\Controllers\PropertyHazardsController;
use Modules\ManagedPremises\Http\Controllers\PropertyServiceWindowsController;
use Modules\ManagedPremises\Http\Controllers\PropertyAssetsController;

use Modules\ManagedPremises\Http\Controllers\PropertyServicePlansController;
use Modules\ManagedPremises\Http\Controllers\PropertyVisitsController;
use Modules\ManagedPremises\Http\Controllers\PropertyInspectionsController;
use Modules\ManagedPremises\Http\Controllers\PropertyDocumentsController;
use Modules\ManagedPremises\Http\Controllers\PropertyApprovalsController;
use Modules\ManagedPremises\Http\Controllers\PropertyCalendarController;

use Modules\ManagedPremises\Http\Controllers\PropertySettingsController;

// Primary: Managed Premises
Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => 'account/premises',
    'as' => 'managedpremises.',
], function () {

    // Dashboard
    Route::get('/', [PropertiesController::class, 'index'])
        ->middleware('permission:managedpremises.view')
        ->name('dashboard');

    // Settings
    Route::get('/settings', [PropertySettingsController::class, 'index'])
        ->middleware('permission:managedpremises.settings')
        ->name('settings.index');
    Route::post('/settings', [PropertySettingsController::class, 'update'])
        ->middleware('permission:managedpremises.settings')
        ->name('settings.update');

    // Properties (policy will enforce per-action perms)
    Route::resource('properties', PropertiesController::class)
        ->middleware('permission:managedpremises.view');

    /*
     |--------------------------------------------------------------------------
     | Global lists (sidebar-friendly)
     |--------------------------------------------------------------------------
     | These routes allow operators to access Rooms/Hazards/Keys/Checklists from
     | the sidebar without first selecting a Site.
     */

    // Rooms
    Route::get('rooms', [PropertyRoomsController::class, 'globalIndex'])
        ->middleware('permission:managedpremises.rooms.view')
        ->name('rooms.index');
    Route::post('rooms', [PropertyRoomsController::class, 'globalStore'])
        ->middleware('permission:managedpremises.rooms.create')
        ->name('rooms.store');

    // Hazards
    Route::get('hazards', [PropertyHazardsController::class, 'globalIndex'])
        ->middleware('permission:managedpremises.hazards.view')
        ->name('hazards.index');
    Route::post('hazards', [PropertyHazardsController::class, 'globalStore'])
        ->middleware('permission:managedpremises.hazards.create')
        ->name('hazards.store');

    // Keys & access
    Route::get('keys', [PropertyKeysController::class, 'globalIndex'])
        ->middleware('permission:managedpremises.keys.view')
        ->name('keys.index');
    Route::post('keys', [PropertyKeysController::class, 'globalStore'])
        ->middleware('permission:managedpremises.keys.create')
        ->name('keys.store');

    // Checklists
    Route::get('checklists', [PropertyChecklistsController::class, 'globalIndex'])
        ->middleware('permission:managedpremises.checklists.view')
        ->name('checklists.index');
    Route::post('checklists', [PropertyChecklistsController::class, 'globalStore'])
        ->middleware('permission:managedpremises.checklists.create')
        ->name('checklists.store');

    // Photos (evidence)
    Route::get('photos', [PropertyPhotosController::class, 'globalIndex'])
        ->middleware('permission:managedpremises.photos.view')
        ->name('photos.index');
    Route::post('photos', [PropertyPhotosController::class, 'globalStore'])
        ->middleware('permission:managedpremises.photos.create')
        ->name('photos.store');
    Route::delete('photos/{photo}', [PropertyPhotosController::class, 'globalDestroy'])
        ->middleware('permission:managedpremises.photos.delete')
        ->name('photos.destroy');

    // Documents (vault)
    Route::get('documents', [PropertyDocumentsController::class, 'globalIndex'])
        ->middleware('permission:managedpremises.documents.view')
        ->name('documents.index');
    Route::post('documents', [PropertyDocumentsController::class, 'globalStore'])
        ->middleware('permission:managedpremises.documents.create')
        ->name('documents.store');

    // Property overview
    Route::get('properties/{property}/overview', [PropertyOverviewController::class, 'show'])
        ->middleware('permission:managedpremises.view')
        ->name('properties.overview');

    // Units
    Route::get('properties/{property}/units', [PropertyUnitsController::class, 'index'])
        ->middleware('permission:managedpremises.units.view')
        ->name('properties.units.index');
    Route::post('properties/{property}/units', [PropertyUnitsController::class, 'store'])
        ->middleware('permission:managedpremises.units.create')
        ->name('properties.units.store');
    Route::delete('properties/{property}/units/{unit}', [PropertyUnitsController::class, 'destroy'])
        ->middleware('permission:managedpremises.units.delete')
        ->name('properties.units.destroy');

    // Contacts
    Route::get('properties/{property}/contacts', [PropertyContactsController::class, 'index'])
        ->middleware('permission:managedpremises.contacts.view')
        ->name('properties.contacts.index');
    Route::post('properties/{property}/contacts', [PropertyContactsController::class, 'store'])
        ->middleware('permission:managedpremises.contacts.create')
        ->name('properties.contacts.store');
    Route::delete('properties/{property}/contacts/{contact}', [PropertyContactsController::class, 'destroy'])
        ->middleware('permission:managedpremises.contacts.delete')
        ->name('properties.contacts.destroy');

    // Jobs (lightweight, optional linking to core later)
    Route::get('properties/{property}/jobs', [PropertyJobsController::class, 'index'])
        ->middleware('permission:managedpremises.jobs.view')
        ->name('properties.jobs.index');
    Route::post('properties/{property}/jobs', [PropertyJobsController::class, 'store'])
        ->middleware('permission:managedpremises.jobs.create')
        ->name('properties.jobs.store');
    Route::delete('properties/{property}/jobs/{job}', [PropertyJobsController::class, 'destroy'])
        ->middleware('permission:managedpremises.jobs.delete')
        ->name('properties.jobs.destroy');

    // Keys & access
    Route::get('properties/{property}/keys', [PropertyKeysController::class, 'index'])
        ->middleware('permission:managedpremises.keys.view')
        ->name('properties.keys.index');
    Route::post('properties/{property}/keys', [PropertyKeysController::class, 'store'])
        ->middleware('permission:managedpremises.keys.create')
        ->name('properties.keys.store');
    Route::delete('properties/{property}/keys/{key}', [PropertyKeysController::class, 'destroy'])
        ->middleware('permission:managedpremises.keys.delete')
        ->name('properties.keys.destroy');

    // Photos
    Route::get('properties/{property}/photos', [PropertyPhotosController::class, 'index'])
        ->middleware('permission:managedpremises.photos.view')
        ->name('properties.photos.index');
    Route::post('properties/{property}/photos', [PropertyPhotosController::class, 'store'])
        ->middleware('permission:managedpremises.photos.create')
        ->name('properties.photos.store');
    Route::delete('properties/{property}/photos/{photo}', [PropertyPhotosController::class, 'destroy'])
        ->middleware('permission:managedpremises.photos.delete')
        ->name('properties.photos.destroy');

    // Checklists
    Route::get('properties/{property}/checklists', [PropertyChecklistsController::class, 'index'])
        ->middleware('permission:managedpremises.checklists.view')
        ->name('properties.checklists.index');
    Route::post('properties/{property}/checklists', [PropertyChecklistsController::class, 'store'])
        ->middleware('permission:managedpremises.checklists.create')
        ->name('properties.checklists.store');
    Route::delete('properties/{property}/checklists/{checklist}', [PropertyChecklistsController::class, 'destroy'])
        ->middleware('permission:managedpremises.checklists.delete')
        ->name('properties.checklists.destroy');

    // Tags
    Route::get('properties/{property}/tags', [PropertyTagsController::class, 'index'])
        ->middleware('permission:managedpremises.tags.view')
        ->name('properties.tags.index');
    Route::post('properties/{property}/tags', [PropertyTagsController::class, 'store'])
        ->middleware('permission:managedpremises.tags.create')
        ->name('properties.tags.store');
    Route::delete('properties/{property}/tags/{tag}', [PropertyTagsController::class, 'destroy'])
        ->middleware('permission:managedpremises.tags.delete')
        ->name('properties.tags.destroy');

    // Rooms
    Route::get('properties/{property}/rooms', [PropertyRoomsController::class, 'index'])
        ->middleware('permission:managedpremises.rooms.view')
        ->name('properties.rooms.index');
    Route::post('properties/{property}/rooms', [PropertyRoomsController::class, 'store'])
        ->middleware('permission:managedpremises.rooms.create')
        ->name('properties.rooms.store');
    Route::delete('properties/{property}/rooms/{room}', [PropertyRoomsController::class, 'destroy'])
        ->middleware('permission:managedpremises.rooms.delete')
        ->name('properties.rooms.destroy');

    // Hazards
    Route::get('properties/{property}/hazards', [PropertyHazardsController::class, 'index'])
        ->middleware('permission:managedpremises.hazards.view')
        ->name('properties.hazards.index');
    Route::post('properties/{property}/hazards', [PropertyHazardsController::class, 'store'])
        ->middleware('permission:managedpremises.hazards.create')
        ->name('properties.hazards.store');
    Route::delete('properties/{property}/hazards/{hazard}', [PropertyHazardsController::class, 'destroy'])
        ->middleware('permission:managedpremises.hazards.delete')
        ->name('properties.hazards.destroy');

    // Service windows
    Route::get('properties/{property}/service-windows', [PropertyServiceWindowsController::class, 'index'])
        ->middleware('permission:managedpremises.servicewindows.view')
        ->name('properties.service_windows.index');
    Route::post('properties/{property}/service-windows', [PropertyServiceWindowsController::class, 'store'])
        ->middleware('permission:managedpremises.servicewindows.create')
        ->name('properties.service_windows.store');
    Route::delete('properties/{property}/service-windows/{window}', [PropertyServiceWindowsController::class, 'destroy'])
        ->middleware('permission:managedpremises.servicewindows.delete')
        ->name('properties.service_windows.destroy');

    // On-site assets register
    Route::get('properties/{property}/assets', [PropertyAssetsController::class, 'index'])
        ->middleware('permission:managedpremises.assets.view')
        ->name('properties.assets.index');
    Route::post('properties/{property}/assets', [PropertyAssetsController::class, 'store'])
        ->middleware('permission:managedpremises.assets.create')
        ->name('properties.assets.store');
    Route::delete('properties/{property}/assets/{asset}', [PropertyAssetsController::class, 'destroy'])
        ->middleware('permission:managedpremises.assets.delete')
        ->name('properties.assets.destroy');

    // Service plans
    Route::resource('properties.service-plans', PropertyServicePlansController::class)
        ->shallow()
        ->middleware('permission:managedpremises.plans.view');

    // Visits
    Route::resource('properties.visits', PropertyVisitsController::class)
        ->shallow()
        ->middleware('permission:managedpremises.visits.view');

    // Inspections
    Route::resource('properties.inspections', PropertyInspectionsController::class)
        ->shallow()
        ->middleware('permission:managedpremises.inspections.view');

    // Documents vault
    Route::get('properties/{property}/documents', [PropertyDocumentsController::class, 'index'])
        ->middleware('permission:managedpremises.documents.view')
        ->name('properties.documents.index');
    Route::post('properties/{property}/documents', [PropertyDocumentsController::class, 'store'])
        ->middleware('permission:managedpremises.documents.create')
        ->name('properties.documents.store');
    Route::get('properties/{property}/documents/{document}', [PropertyDocumentsController::class, 'download'])
        ->middleware('permission:managedpremises.documents.view')
        ->name('properties.documents.download');
    Route::delete('properties/{property}/documents/{document}', [PropertyDocumentsController::class, 'destroy'])
        ->middleware('permission:managedpremises.documents.delete')
        ->name('properties.documents.destroy');

    // Approvals
    Route::get('properties/{property}/approvals', [PropertyApprovalsController::class, 'index'])
        ->middleware('permission:managedpremises.approvals.view')
        ->name('properties.approvals.index');
    Route::post('properties/{property}/approvals', [PropertyApprovalsController::class, 'store'])
        ->middleware('permission:managedpremises.approvals.create')
        ->name('properties.approvals.store');
    Route::post('properties/{property}/approvals/{approval}/decision', [PropertyApprovalsController::class, 'decision'])
        ->middleware('permission:managedpremises.approvals.update')
        ->name('properties.approvals.decision');

    // Calendar
    Route::get('calendar', [PropertyCalendarController::class, 'index'])
        ->middleware('permission:managedpremises.calendar.view')
        ->name('calendar.index');
});

// Backward compatibility: old /account/sites paths redirect to /account/premises
Route::middleware(['web','auth'])->prefix('account/sites')->group(function () {
    Route::redirect('/', '/account/premises', 302);
    Route::redirect('/rooms', '/account/premises/rooms', 302);
    Route::redirect('/hazards', '/account/premises/hazards', 302);
    Route::redirect('/keys', '/account/premises/keys', 302);
    Route::redirect('/checklists', '/account/premises/checklists', 302);
    Route::redirect('/photos', '/account/premises/photos', 302);
    Route::redirect('/documents', '/account/premises/documents', 302);
});