<?php
use Illuminate\Support\Facades\Route;

use Modules\TitanTalk\Http\Controllers\IntentController;
use Modules\TitanTalk\Http\Controllers\EntityController;
use Modules\TitanTalk\Http\Controllers\TrainingPhraseController;

Route::middleware(['web','auth'])->prefix('aiconverse')->group(function () {
    // Intents
    Route::get('/intents', [IntentController::class, 'index'])->name('titantalk.intents.index');
    Route::get('/intents/create', 'IntentController@create')->name('titantalk.intents.create');
    Route::post('/intents', [IntentController::class, 'store'])->name('titantalk.intents.store');
    Route::get('/intents/{id}/edit', [IntentController::class, 'edit'])->name('titantalk.intents.edit');
    Route::put('/intents/{id}', [IntentController::class, 'update'])->name('titantalk.intents.update');
    Route::delete('/intents/{id}', [IntentController::class, 'destroy'])->name('titantalk.intents.delete');
    Route::get('/intents/export', [IntentController::class, 'export'])->name('titantalk.intents.export');
    Route::post('/intents/import', [IntentController::class, 'import'])->name('titantalk.intents.import');

    // Entities
    Route::get('/entities', [EntityController::class, 'index'])->name('titantalk.entities.index');
    Route::get('/entities/create', 'EntityController@create')->name('titantalk.entities.create');
    Route::post('/entities', [EntityController::class, 'store'])->name('titantalk.entities.store');
    Route::get('/entities/{id}/edit', [EntityController::class, 'edit'])->name('titantalk.entities.edit');
    Route::put('/entities/{id}', [EntityController::class, 'update'])->name('titantalk.entities.update');
    Route::delete('/entities/{id}', [EntityController::class, 'destroy'])->name('titantalk.entities.delete');
    Route::get('/entities/export', [EntityController::class, 'export'])->name('titantalk.entities.export');
    Route::post('/entities/import', [EntityController::class, 'import'])->name('titantalk.entities.import');

    // Training phrases
    Route::get('/training', [TrainingPhraseController::class, 'index'])->name('titantalk.training.index');
    Route::post('/training', [TrainingPhraseController::class, 'store'])->name('titantalk.training.store');
    Route::delete('/training/{id}', [TrainingPhraseController::class, 'destroy'])->name('titantalk.training.delete');
    Route::get('/training/export', [TrainingPhraseController::class, 'export'])->name('titantalk.training.export');
});