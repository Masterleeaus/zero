<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanTalk\Http\Controllers\ConversationController;
use Modules\TitanTalk\Http\Controllers\DashboardController;
use Modules\TitanTalk\Http\Controllers\SettingsController;
use Modules\TitanTalk\Http\Controllers\VoiceBotController;

// Web admin settings + Titan Talk management
Route::middleware(['web','auth'])->prefix('aiconverse')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('titantalk.settings');
    Route::post('/settings', [SettingsController::class, 'save'])->name('titantalk.settings.save');

    // Titan Talk – Conversations CRM linking
    Route::get('/conversations', [ConversationController::class, 'index'])->name('titantalk.conversations.index');
    Route::put('/conversations/{conversation}/crm', [ConversationController::class, 'updateCrm'])->name('titantalk.conversations.update-crm');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('titantalk.conversations.show');

    // Titan Talk – Voice Bot manager
    Route::get('/voice-bots', [VoiceBotController::class, 'index'])->name('titantalk.voice-bots.index');
    Route::get('/voice-bots/create', [VoiceBotController::class, 'create'])->name('titantalk.voice-bots.create');
    Route::post('/voice-bots', [VoiceBotController::class, 'store'])->name('titantalk.voice-bots.store');
    Route::get('/voice-bots/{voice_bot}/edit', [VoiceBotController::class, 'edit'])->name('titantalk.voice-bots.edit');
    Route::put('/voice-bots/{voice_bot}', [VoiceBotController::class, 'update'])->name('titantalk.voice-bots.update');
    Route::delete('/voice-bots/{voice_bot}', [VoiceBotController::class, 'destroy'])->name('titantalk.voice-bots.destroy');

    // Titan Talk – Dashboard
    // NOTE: The primary dashboard route name is defined in Routes/web.php as
    // `titantalk.dashboard` (account/titantalk). Keep this one non-conflicting.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('titantalk.dashboard.admin');
});


