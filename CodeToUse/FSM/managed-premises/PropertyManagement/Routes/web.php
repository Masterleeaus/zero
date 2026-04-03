<?php

use Illuminate\Support\Facades\Route;

use Modules\PropertyManagement\Http\Controllers\PropertiesController;
use Modules\PropertyManagement\Http\Controllers\PropertyOverviewController;
use Modules\PropertyManagement\Http\Controllers\PropertyUnitsController;
use Modules\PropertyManagement\Http\Controllers\PropertyContactsController;
use Modules\PropertyManagement\Http\Controllers\PropertyJobsController;
use Modules\PropertyManagement\Http\Controllers\PropertyKeysController;
use Modules\PropertyManagement\Http\Controllers\PropertyPhotosController;
use Modules\PropertyManagement\Http\Controllers\PropertyChecklistsController;
use Modules\PropertyManagement\Http\Controllers\PropertyTagsController;
use Modules\PropertyManagement\Http\Controllers\PropertyRoomsController;
use Modules\PropertyManagement\Http\Controllers\PropertyHazardsController;
use Modules\PropertyManagement\Http\Controllers\PropertyServiceWindowsController;
use Modules\PropertyManagement\Http\Controllers\PropertyAssetsController;

use Modules\PropertyManagement\Http\Controllers\PropertyServicePlansController;
use Modules\PropertyManagement\Http\Controllers\PropertyVisitsController;
use Modules\PropertyManagement\Http\Controllers\PropertyInspectionsController;
use Modules\PropertyManagement\Http\Controllers\PropertyDocumentsController;
use Modules\PropertyManagement\Http\Controllers\PropertyApprovalsController;
use Modules\PropertyManagement\Http\Controllers\PropertyCalendarController;

use Modules\PropertyManagement\Http\Controllers\PropertySettingsController;

Route::group([
    'middleware' => ['web', 'auth'],
    'prefix' => 'account/property-management',
    'as' => 'propertymanagement.',
], function () {

    // Dashboard
    Route::get('/', [PropertiesController::class, 'index'])
        ->middleware('permission:propertymanagement.view')
        ->name('dashboard');

    // Settings
    Route::get('/settings', [PropertySettingsController::class, 'index'])
        ->middleware('permission:propertymanagement.settings')
        ->name('settings.index');
    Route::post('/settings', [PropertySettingsController::class, 'update'])
        ->middleware('permission:propertymanagement.settings')
        ->name('settings.update');

    // Properties (policy will enforce per-action perms)
    Route::resource('properties', PropertiesController::class)
        ->middleware('permission:propertymanagement.view');

    // Property overview
    Route::get('properties/{property}/overview', [PropertyOverviewController::class, 'show'])
        ->middleware('permission:propertymanagement.view')
        ->name('properties.overview');

    // Units
    Route::get('properties/{property}/units', [PropertyUnitsController::class, 'index'])
        ->middleware('permission:propertymanagement.units.view')
        ->name('properties.units.index');
    Route::post('properties/{property}/units', [PropertyUnitsController::class, 'store'])
        ->middleware('permission:propertymanagement.units.create')
        ->name('properties.units.store');
    Route::delete('properties/{property}/units/{unit}', [PropertyUnitsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.units.delete')
        ->name('properties.units.destroy');

    // Contacts
    Route::get('properties/{property}/contacts', [PropertyContactsController::class, 'index'])
        ->middleware('permission:propertymanagement.contacts.view')
        ->name('properties.contacts.index');
    Route::post('properties/{property}/contacts', [PropertyContactsController::class, 'store'])
        ->middleware('permission:propertymanagement.contacts.create')
        ->name('properties.contacts.store');
    Route::delete('properties/{property}/contacts/{contact}', [PropertyContactsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.contacts.delete')
        ->name('properties.contacts.destroy');

    // Jobs (lightweight, optional linking to core later)
    Route::get('properties/{property}/jobs', [PropertyJobsController::class, 'index'])
        ->middleware('permission:propertymanagement.jobs.view')
        ->name('properties.jobs.index');
    Route::post('properties/{property}/jobs', [PropertyJobsController::class, 'store'])
        ->middleware('permission:propertymanagement.jobs.create')
        ->name('properties.jobs.store');
    Route::delete('properties/{property}/jobs/{job}', [PropertyJobsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.jobs.delete')
        ->name('properties.jobs.destroy');

    // Keys & access
    Route::get('properties/{property}/keys', [PropertyKeysController::class, 'index'])
        ->middleware('permission:propertymanagement.keys.view')
        ->name('properties.keys.index');
    Route::post('properties/{property}/keys', [PropertyKeysController::class, 'store'])
        ->middleware('permission:propertymanagement.keys.create')
        ->name('properties.keys.store');
    Route::delete('properties/{property}/keys/{key}', [PropertyKeysController::class, 'destroy'])
        ->middleware('permission:propertymanagement.keys.delete')
        ->name('properties.keys.destroy');

    // Photos
    Route::get('properties/{property}/photos', [PropertyPhotosController::class, 'index'])
        ->middleware('permission:propertymanagement.photos.view')
        ->name('properties.photos.index');
    Route::post('properties/{property}/photos', [PropertyPhotosController::class, 'store'])
        ->middleware('permission:propertymanagement.photos.create')
        ->name('properties.photos.store');
    Route::delete('properties/{property}/photos/{photo}', [PropertyPhotosController::class, 'destroy'])
        ->middleware('permission:propertymanagement.photos.delete')
        ->name('properties.photos.destroy');

    // Checklists
    Route::get('properties/{property}/checklists', [PropertyChecklistsController::class, 'index'])
        ->middleware('permission:propertymanagement.checklists.view')
        ->name('properties.checklists.index');
    Route::post('properties/{property}/checklists', [PropertyChecklistsController::class, 'store'])
        ->middleware('permission:propertymanagement.checklists.create')
        ->name('properties.checklists.store');
    Route::delete('properties/{property}/checklists/{checklist}', [PropertyChecklistsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.checklists.delete')
        ->name('properties.checklists.destroy');

    // Tags
    Route::get('properties/{property}/tags', [PropertyTagsController::class, 'index'])
        ->middleware('permission:propertymanagement.tags.view')
        ->name('properties.tags.index');
    Route::post('properties/{property}/tags', [PropertyTagsController::class, 'store'])
        ->middleware('permission:propertymanagement.tags.create')
        ->name('properties.tags.store');
    Route::delete('properties/{property}/tags/{tag}', [PropertyTagsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.tags.delete')
        ->name('properties.tags.destroy');

    // Rooms
    Route::get('properties/{property}/rooms', [PropertyRoomsController::class, 'index'])
        ->middleware('permission:propertymanagement.rooms.view')
        ->name('properties.rooms.index');
    Route::post('properties/{property}/rooms', [PropertyRoomsController::class, 'store'])
        ->middleware('permission:propertymanagement.rooms.create')
        ->name('properties.rooms.store');
    Route::delete('properties/{property}/rooms/{room}', [PropertyRoomsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.rooms.delete')
        ->name('properties.rooms.destroy');

    // Hazards
    Route::get('properties/{property}/hazards', [PropertyHazardsController::class, 'index'])
        ->middleware('permission:propertymanagement.hazards.view')
        ->name('properties.hazards.index');
    Route::post('properties/{property}/hazards', [PropertyHazardsController::class, 'store'])
        ->middleware('permission:propertymanagement.hazards.create')
        ->name('properties.hazards.store');
    Route::delete('properties/{property}/hazards/{hazard}', [PropertyHazardsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.hazards.delete')
        ->name('properties.hazards.destroy');

    // Service windows
    Route::get('properties/{property}/service-windows', [PropertyServiceWindowsController::class, 'index'])
        ->middleware('permission:propertymanagement.servicewindows.view')
        ->name('properties.service_windows.index');
    Route::post('properties/{property}/service-windows', [PropertyServiceWindowsController::class, 'store'])
        ->middleware('permission:propertymanagement.servicewindows.create')
        ->name('properties.service_windows.store');
    Route::delete('properties/{property}/service-windows/{window}', [PropertyServiceWindowsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.servicewindows.delete')
        ->name('properties.service_windows.destroy');

    // On-site assets register
    Route::get('properties/{property}/assets', [PropertyAssetsController::class, 'index'])
        ->middleware('permission:propertymanagement.assets.view')
        ->name('properties.assets.index');
    Route::post('properties/{property}/assets', [PropertyAssetsController::class, 'store'])
        ->middleware('permission:propertymanagement.assets.create')
        ->name('properties.assets.store');
    Route::delete('properties/{property}/assets/{asset}', [PropertyAssetsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.assets.delete')
        ->name('properties.assets.destroy');

    // Service plans
    Route::resource('properties.service-plans', PropertyServicePlansController::class)
        ->shallow()
        ->middleware('permission:propertymanagement.plans.view');

    // Visits
    Route::resource('properties.visits', PropertyVisitsController::class)
        ->shallow()
        ->middleware('permission:propertymanagement.visits.view');

    // Inspections
    Route::resource('properties.inspections', PropertyInspectionsController::class)
        ->shallow()
        ->middleware('permission:propertymanagement.inspections.view');

    // Documents vault
    Route::get('properties/{property}/documents', [PropertyDocumentsController::class, 'index'])
        ->middleware('permission:propertymanagement.documents.view')
        ->name('properties.documents.index');
    Route::post('properties/{property}/documents', [PropertyDocumentsController::class, 'store'])
        ->middleware('permission:propertymanagement.documents.create')
        ->name('properties.documents.store');
    Route::get('properties/{property}/documents/{document}', [PropertyDocumentsController::class, 'download'])
        ->middleware('permission:propertymanagement.documents.view')
        ->name('properties.documents.download');
    Route::delete('properties/{property}/documents/{document}', [PropertyDocumentsController::class, 'destroy'])
        ->middleware('permission:propertymanagement.documents.delete')
        ->name('properties.documents.destroy');

    // Approvals
    Route::get('properties/{property}/approvals', [PropertyApprovalsController::class, 'index'])
        ->middleware('permission:propertymanagement.approvals.view')
        ->name('properties.approvals.index');
    Route::post('properties/{property}/approvals', [PropertyApprovalsController::class, 'store'])
        ->middleware('permission:propertymanagement.approvals.create')
        ->name('properties.approvals.store');
    Route::post('properties/{property}/approvals/{approval}/decision', [PropertyApprovalsController::class, 'decision'])
        ->middleware('permission:propertymanagement.approvals.update')
        ->name('properties.approvals.decision');

    // Calendar
    Route::get('calendar', [PropertyCalendarController::class, 'index'])
        ->middleware('permission:propertymanagement.calendar.view')
        ->name('calendar.index');
});