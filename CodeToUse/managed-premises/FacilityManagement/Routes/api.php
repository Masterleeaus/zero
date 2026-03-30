<?php

use Illuminate\Support\Facades\Route;
use Modules\FacilityManagement\Http\Controllers\Api\FacilityApiController;

Route::get('facility/sites', [FacilityApiController::class, 'sites']);
Route::post('facility/inspections/{id}/complete', [FacilityApiController::class, 'completeInspection']);
Route::post('facility/meters/{id}/read', [FacilityApiController::class, 'readMeter']);
Route::post('facility/import', [FacilityApiController::class, 'import']);
