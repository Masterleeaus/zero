<?php

/**
 * ADD THIS TO ChatbotWhatsappServiceProvider.php in the registerRoutes() method
 * This snippet replaces the existing Twilio webhook routes
 */

// Updated route registration that handles SMS, WhatsApp, and Voice
$this->router()
    ->group([
        'middleware' => ['api'],
        'prefix'     => 'api/v2/chatbot',
        'as'         => 'api.v2.chatbot.',
    ], function (Router $router) {
        
        // Main webhook endpoint - routes to ChatbotTwilioController
        // Handles SMS, WhatsApp, and Voice call initiation
        $router->post(
            'channel/twilio/{chatbotId}/{channelId}',
            [ChatbotTwilioController::class, 'handle']
        )->name('channel.twilio.post.handle');

        // Voice call transcript processing
        // Called when Gather ends (user finished speaking)
        $router->post(
            '{chatbot:uuid}/voice/transcript/{conversation}/{channelId}',
            [VoiceCallController::class, 'transcript']
        )->name('voice.transcript');

        // Voice call status callback
        // Called when call state changes
        $router->post(
            '{chatbot:uuid}/voice/status/{conversation}/{channelId}',
            [VoiceCallController::class, 'statusCallback']
        )->name('voice.status');

        // Voice call recording callback
        // Called when recording is available
        $router->post(
            '{chatbot:uuid}/voice/recording/{conversation}/{channelId}',
            [VoiceCallController::class, 'recordingCallback']
        )->name('voice.recording');

        // Voice call end endpoint
        // Gracefully ends call
        $router->post(
            '{chatbot:uuid}/voice/end/{conversation}/{channelId}',
            [VoiceCallController::class, 'endCall']
        )->name('voice.end');
    });

/**
 * IMPORTANT: Also add the controller imports at the top of ChatbotWhatsappServiceProvider:
 * 
 * use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\ChatbotTwilioController;
 * use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\VoiceCallController;
 */
