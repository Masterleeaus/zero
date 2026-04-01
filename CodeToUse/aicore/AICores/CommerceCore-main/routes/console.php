<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// AI ENGINE SCHEDULED JOBS
// ==========================================
Schedule::call(function () {
    app(\App\Services\AI\SalesAIEngine::class)->analyzeAll();
})->daily()->at('06:00')->name('ai:sales-analysis');

Schedule::call(function () {
    app(\App\Services\AI\InventoryAIEngine::class)->analyzeAll();
})->daily()->at('07:00')->name('ai:inventory-analysis');

Schedule::call(function () {
    app(\App\Services\AI\FraudAIEngine::class)->analyzeAll();
})->daily()->at('08:00')->name('ai:fraud-analysis');

// ==========================================
// DOGWATCH SYSTEM HEALTH CHECKS
// ==========================================
Schedule::call(function () {
    app(\App\Services\DogwatchEngine::class)->runAll();
})->everyFourHours()->name('dogwatch:health-check');
