<?php

use App\Http\Controllers\Dashboard\ChatbotCommandController;
use App\Http\Controllers\Dashboard\ChatbotAgentController;
use App\Http\Controllers\Portal\ChatbotCustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ChatBot Routes (Owner/Agent/Customer)
|--------------------------------------------------------------------------
|
| This file contains all routes for the unified chatbot system
| integrated with MagicAI's authentication and multi-tenancy.
|
*/

// =====================================================================
// AUTHENTICATED ROUTES (All require: auth, workspace.active)
// =====================================================================
Route::middleware(['web', 'auth', 'verified', 'workspace.active'])
    ->group(function () {

        // =====================================================================
        // OWNER DASHBOARD (Titan Command)
        // =====================================================================
        Route::middleware('can:manage-chatbots')
            ->prefix('dashboard/chatbots')
            ->name('dashboard.chatbots.')
            ->controller(ChatbotCommandController::class)
            ->group(function () {
                // List all chatbots
                Route::get('', 'index')
                    ->name('index');

                // Create chatbot form
                Route::get('create', 'create')
                    ->name('create');

                // Store new chatbot
                Route::post('', 'store')
                    ->name('store');

                // View chatbot detail
                Route::get('{chatbot}', 'show')
                    ->name('show')
                    ->where('chatbot', '[0-9]+');

                // Edit chatbot form
                Route::get('{chatbot}/edit', 'edit')
                    ->name('edit')
                    ->where('chatbot', '[0-9]+');

                // Update chatbot
                Route::put('{chatbot}', 'update')
                    ->name('update')
                    ->where('chatbot', '[0-9]+');

                // Delete chatbot
                Route::delete('{chatbot}', 'destroy')
                    ->name('destroy')
                    ->where('chatbot', '[0-9]+');

                // Get analytics data (API)
                Route::get('{chatbot}/analytics', 'analytics')
                    ->name('analytics')
                    ->where('chatbot', '[0-9]+');

                // Get conversations for chatbot (API)
                Route::get('{chatbot}/conversations', 'conversations')
                    ->name('conversations')
                    ->where('chatbot', '[0-9]+');

                // Assign agent to chatbot
                Route::post('{chatbot}/assign-agent', 'assignAgent')
                    ->name('assign-agent')
                    ->where('chatbot', '[0-9]+');

                // Get webhook URL
                Route::get('{chatbot}/webhook-url/{channel}', 'webhookUrl')
                    ->name('webhook-url')
                    ->where('chatbot', '[0-9]+');
            });

        // =====================================================================
        // AGENT PANEL (Titan Go)
        // =====================================================================
        Route::middleware('can:respond-to-conversations')
            ->prefix('dashboard/agent')
            ->name('dashboard.agent.')
            ->controller(ChatbotAgentController::class)
            ->group(function () {
                // Agent's conversation list
                Route::get('', 'index')
                    ->name('index');

                // View single conversation
                Route::get('conversations/{conversation}', 'show')
                    ->name('show')
                    ->where('conversation', '[0-9]+');

                // Agent replies to conversation
                Route::post('conversations/{conversation}/reply', 'reply')
                    ->name('reply')
                    ->where('conversation', '[0-9]+');

                // Transfer conversation to another agent
                Route::post('conversations/{conversation}/transfer', 'transfer')
                    ->name('transfer')
                    ->where('conversation', '[0-9]+');

                // Close/resolve conversation
                Route::post('conversations/{conversation}/close', 'close')
                    ->name('close')
                    ->where('conversation', '[0-9]+');

                // Mark conversation as read
                Route::post('conversations/{conversation}/mark-read', 'markRead')
                    ->name('mark-read')
                    ->where('conversation', '[0-9]+');
            });

        // =====================================================================
        // CUSTOMER PORTAL (Titan Nexus)
        // =====================================================================
        Route::prefix('portal/conversations')
            ->name('portal.conversations.')
            ->controller(ChatbotCustomerController::class)
            ->group(function () {
                // List customer's conversations
                Route::get('', 'index')
                    ->name('index');

                // Create new conversation
                Route::post('', 'create')
                    ->name('create');

                // View single conversation
                Route::get('{conversation}', 'show')
                    ->name('show')
                    ->where('conversation', '[0-9]+');

                // Customer sends message
                Route::post('{conversation}/message', 'sendMessage')
                    ->name('message.store')
                    ->where('conversation', '[0-9]+');

                // Reopen closed conversation
                Route::post('{conversation}/reopen', 'reopen')
                    ->name('reopen')
                    ->where('conversation', '[0-9]+');

                // Rate/provide feedback
                Route::post('{conversation}/feedback', 'rateFeedback')
                    ->name('feedback')
                    ->where('conversation', '[0-9]+');

                // Export conversation
                Route::get('{conversation}/export', 'export')
                    ->name('export')
                    ->where('conversation', '[0-9]+');
            });
    });

// =====================================================================
// API ROUTES (Internal - Requires Auth)
// =====================================================================
Route::middleware(['api', 'auth:sanctum'])
    ->prefix('api/v1/chatbot')
    ->name('api.chatbot.')
    ->group(function () {
        // Agent API endpoints
        Route::middleware('can:respond-to-conversations')
            ->prefix('agent')
            ->controller(ChatbotAgentController::class)
            ->group(function () {
                Route::get('conversations', 'list')->name('agent.list');
                Route::get('conversations/{conversation}', 'show')->name('agent.show');
                Route::post('conversations/{conversation}/reply', 'reply')->name('agent.reply');
                Route::post('conversations/{conversation}/transfer', 'transfer')->name('agent.transfer');
                Route::post('conversations/{conversation}/close', 'close')->name('agent.close');
                Route::get('unread-count', 'unreadCount')->name('agent.unread');
            });

        // Customer API endpoints
        Route::prefix('customer')
            ->controller(ChatbotCustomerController::class)
            ->group(function () {
                Route::get('conversations', 'list')->name('customer.list');
                Route::get('conversations/{conversation}', 'getConversation')->name('customer.show');
                Route::post('conversations', 'create')->name('customer.create');
                Route::post('conversations/{conversation}/message', 'sendMessage')->name('customer.message');
                Route::get('conversations/{conversation}/quick-replies', 'quickReplies')->name('customer.quick-replies');
            });
    });

// =====================================================================
// PUBLIC WEBHOOK ROUTES (No Auth Required)
// =====================================================================
Route::middleware(['throttle:chatbot-webhook'])
    ->prefix('api/v1/chatbot-webhook')
    ->group(function () {
        // Generic webhook handler for all channels
        Route::post('{chatbot}/channel/{channel}', [
            \App\Http\Controllers\Api\ChatbotWebhookController::class,
            'handle'
        ])->name('api.chatbot.webhook');

        // Specific channel webhooks (optional, for clarity)
        Route::post('telegram/{chatbot}', [\App\Extensions\ChatbotTelegram\System\Http\Controllers\Webhook\ChatbotTelegramWebhookController::class, 'handle'])
            ->name('api.chatbot.webhook.telegram');

        Route::post('whatsapp/{chatbot}', [\App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\ChatbotTwilioController::class, 'handle'])
            ->name('api.chatbot.webhook.whatsapp');

        Route::post('messenger/{chatbot}', [\App\Extensions\ChatbotMessenger\System\Http\Controllers\Webhook\ChatbotMessengerWebhookController::class, 'handle'])
            ->name('api.chatbot.webhook.messenger');

        Route::post('voice/{chatbot}', [\App\Extensions\ChatbotVoice\System\Http\Controllers\Webhook\ChatbotVoiceWebhookController::class, 'handle'])
            ->name('api.chatbot.webhook.voice');
    });

// =====================================================================
// ADMIN/OWNER ANALYTICS ROUTES (Optional)
// =====================================================================
Route::middleware(['web', 'auth', 'verified', 'workspace.active', 'can:manage-chatbots'])
    ->prefix('dashboard/chatbots-analytics')
    ->name('dashboard.chatbots-analytics.')
    ->group(function () {
        Route::get('', function () {
            // Show workspace-wide chatbot analytics
            return view('dashboard.chatbots-analytics.index');
        })->name('index');

        Route::get('export', function () {
            // Export analytics report
            return view('dashboard.chatbots-analytics.export');
        })->name('export');
    });
