<?php

use Illuminate\Support\Facades\Route;
use Modules\FacilityManagement\Http\Controllers\*;
use Modules\FacilityManagement\Http\Middleware\FacilityTenantScope;

// Dashboard
Route::get('facility', [DashboardController::class, 'index'])->name('facility.dashboard');

// Core resources
Route::resources([
    'sites'      => SitesController::class,
    'buildings'  => BuildingsController::class,
    'units'      => UnitsController::class,
    'unit-types' => UnitTypesController::class,
    'assets'     => AssetsController::class,
    'inspections'=> InspectionsController::class,
    'docs'       => DocsController::class,
    'meters'     => MetersController::class,
    'reads'      => MeterReadsController::class,
    'occupancy'  => OccupancyController::class,
]);

// AI helpers
Route::post('units/{id}/ai-checklist', [AiFacilityController::class, 'unitChecklist'])->name('facility.ai.unit.checklist');
Route::post('assets/{id}/ai-pm', [AiFacilityController::class, 'assetPmPlan'])->name('facility.ai.asset.pm');
Route::post('docs/{id}/ai-summary', [AiFacilityController::class, 'docSummary'])->name('facility.ai.doc.summary');

Route::middleware([FacilityTenantScope::class])->group(function(){
  // resources already defined above; this encloses them if duplicated
});


use Modules\FacilityManagement\Http\Controllers\ImportController;

Route::get('facility/import', [ImportController::class, 'form'])->name('facility.import.form');
Route::post('facility/import', [ImportController::class, 'upload'])->name('facility.import.upload');


use Modules\FacilityManagement\Http\Controllers\ExportController;
Route::get('facility/export/{entity}', [ExportController::class, 'csv'])->name('facility.export.csv');


use Modules\FacilityManagement\Http\Controllers\WorkOrderBridgeController;
Route::post('inspections/{id}/create-work-order', [WorkOrderBridgeController::class, 'createFromInspection'])->name('facility.inspections.createWorkOrder');
Route::post('assets/{id}/create-work-order', [WorkOrderBridgeController::class, 'createFromAsset'])->name('facility.assets.createWorkOrder');


use Modules\FacilityManagement\Http\Controllers\EnergyTrendController;
Route::get('facility/energy/trend.csv', [EnergyTrendController::class, 'csv'])->name('facility.energy.csv');
Route::get('facility/energy/trend.svg', [EnergyTrendController::class, 'svg'])->name('facility.energy.svg');


use Modules\FacilityManagement\Http\Controllers\ReportsController;
Route::prefix('facility/reports')->group(function(){
    Route::get('building-energy.csv', [ReportsController::class, 'buildingEnergyCsv'])->name('facility.reports.buildingEnergyCsv');
    Route::get('inspection-sla.csv', [ReportsController::class, 'slaCsv'])->name('facility.reports.slaCsv');
    Route::get('building-occupancy.csv', [ReportsController::class, 'occupancyCsv'])->name('facility.reports.occupancyCsv');
});


use Modules\FacilityManagement\Http\Controllers\BuildingDashboardController;
use Modules\FacilityManagement\Http\Controllers\UnitTimelineController;
use Modules\FacilityManagement\Http\Middleware\FacilityReportsAccess;

Route::get('buildings/{id}/dashboard', [BuildingDashboardController::class, 'show'])->name('facility.buildings.dashboard');
Route::get('units/{id}/timeline', [UnitTimelineController::class, 'show'])->name('facility.units.timeline');

// Protect reports with permission middleware
Route::middleware([FacilityReportsAccess::class])->group(function(){
    Route::get('facility/reports', function(){ return view('facility::reports.index'); })->name('facility.reports.index');
});


use Modules\FacilityManagement\Http\Controllers\BuildingEnergyController;
Route::get('buildings/{id}/energy', [BuildingEnergyController::class, 'show'])->name('facility.buildings.energy');


use Modules\FacilityManagement\Http\Controllers\CompliancePackController;
Route::get('buildings/{id}/compliance-pack', [CompliancePackController::class, 'building'])->name('facility.buildings.compliancePack');
