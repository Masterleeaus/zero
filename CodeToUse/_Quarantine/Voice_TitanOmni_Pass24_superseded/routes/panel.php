<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('dashboard/user/omni')->name('dashboard.user.omni.')->group(function () {
    Route::post('/conversation', [\App\Http\Controllers\Omni\OmniConversationController::class, 'store'])->name('conversation.store');
});

Route::middleware(['web', 'auth'])->prefix('dashboard/user/omni/legacy')->name('dashboard.user.omni.legacy.')->group(function () {
    Route::post('/mirror/{driver}', [\App\Http\Controllers\Omni\OmniLegacyMirrorController::class, 'store'])->name('mirror.store');
});
