<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\Admin\AIController;

// Mobile App Authentication
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Public Store Endpoints (Tenant aware via store_domain parameter or header, simplified to parameter)
    Route::get('/stores/{storeDomain}/products', [ProductController::class, 'index']);
    Route::get('/stores/{storeDomain}/products/{id}', [ProductController::class, 'show']);

    // Protected Customer Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
    });
});

// Admin API endpoints (Internal)
Route::middleware(['auth:sanctum', 'role:Super Admin|Store Owner'])->prefix('admin')->group(function () {
    Route::post('/ai/generate-description', [AIController::class, 'generateDescription']);
});
