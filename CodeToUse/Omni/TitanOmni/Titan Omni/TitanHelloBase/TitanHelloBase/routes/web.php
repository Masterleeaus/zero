<?php

use Illuminate\Support\Facades\Route;
use Extensions\TitanHello\Controllers\ChatbotVoiceController;
use Extensions\TitanHello\Controllers\ChatbotVoiceTrainController;
use Extensions\TitanHello\Controllers\ChatbotVoiceHistoryController;
use Extensions\TitanHello\Controllers\AvatarController;
use Extensions\TitanHello\Controllers\SettingsController;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin/extensions/titan-hello')
    ->name('admin.extensions.titan-hello.')
    ->group(function () {
        Route::get('/', [ChatbotVoiceController::class, 'index'])->name('index');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/save', [SettingsController::class, 'save'])->name('settings.save');

        // Agents (legacy controller names retained for stability)
        Route::post('/store', [ChatbotVoiceController::class, 'store'])->name('store');
        Route::put('/update', [ChatbotVoiceController::class, 'update'])->name('update');
        Route::delete('/delete', [ChatbotVoiceController::class, 'delete'])->name('delete');

        // Training
        Route::prefix('train')->name('train.')->group(function () {
            Route::get('/data', [ChatbotVoiceTrainController::class, 'trainData'])->name('data');
            Route::delete('/delete', [ChatbotVoiceTrainController::class, 'delete'])->name('delete');
            Route::post('/generate', [ChatbotVoiceTrainController::class, 'generateEmbedding'])->name('generate');
            Route::post('/file', [ChatbotVoiceTrainController::class, 'trainFile'])->name('file');
            Route::post('/text', [ChatbotVoiceTrainController::class, 'trainText'])->name('text');
            Route::post('/url', [ChatbotVoiceTrainController::class, 'trainUrl'])->name('url');
        });

        // Conversation history
        Route::prefix('conversation')->name('conversation.')->group(function () {
            Route::get('/with-paginate', [ChatbotVoiceHistoryController::class, 'loadConversationWithPaginate'])->name('with-paginate');
        });

        // Avatars
        Route::post('/avatar/upload', AvatarController::class)->name('avatar.upload');
    });
