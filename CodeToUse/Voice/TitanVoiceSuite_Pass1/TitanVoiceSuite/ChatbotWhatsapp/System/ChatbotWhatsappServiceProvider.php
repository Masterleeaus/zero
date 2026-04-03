<?php

declare(strict_types=1);

namespace App\Extensions\ChatbotWhatsapp\System;

use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\ChatbotWhatsappController;
use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\ChatbotTwilioController;
use App\Extensions\ChatbotWhatsapp\System\Http\Controllers\Webhook\VoiceCallController;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ChatbotWhatsappServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerRoutes()
            ->registerMigrations()
            ->publishAssets()
            ->registerComponents();
    }

    public function registerComponents(): static
    {
        return $this;
    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets/icons' => public_path('vendor/whatsapp-channel/icons'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/whatsapp-channel.php', 'whatsapp-channel');
        $this->mergeConfigFrom(__DIR__ . '/../config/unified-communication.php', 'unified-communication');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'whatsapp-channel');

        return $this;
    }

    public function registerViews(): static
    {
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], 'whatsapp-channel');

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    private function registerRoutes(): static
    {
        $this->router()
            ->group([
                'middleware' => ['api'],
                'prefix'     => 'api/v2/chatbot',
                'as'         => 'api.v2.chatbot.',
            ], function (Router $router) {
                // Backwards-compatible webhook route
                $router->any('{chatbotId}/channel/{channelId}/twilio', [ChatbotTwilioController::class, 'handle'])
                    ->name('channel.twilio.legacy.handle');

                // Preferred unified Twilio route
                $router->any('channel/twilio/{chatbotId}/{channelId}', [ChatbotTwilioController::class, 'handle'])
                    ->name('channel.twilio.post.handle');

                // Voice call lifecycle routes
                $router->post('{chatbot:uuid}/voice/transcript/{conversation}/{channelId}', [VoiceCallController::class, 'transcript'])
                    ->name('voice.transcript');
                $router->post('{chatbot:uuid}/voice/status/{conversation}/{channelId}', [VoiceCallController::class, 'statusCallback'])
                    ->name('voice.status');
                $router->post('{chatbot:uuid}/voice/recording/{conversation}/{channelId}', [VoiceCallController::class, 'recordingCallback'])
                    ->name('voice.recording');
                $router->post('{chatbot:uuid}/voice/end/{conversation}/{channelId}', [VoiceCallController::class, 'endCall'])
                    ->name('voice.end');
            })
            ->group([
                'middleware' => ['web', 'auth'],
            ], function (Router $router) {
                $router->controller(ChatbotWhatsappController::class)
                    ->name('dashboard.chatbot-multi-channel.whatsapp.')
                    ->prefix('dashboard/chatbot-multi-channel/whatsapp')
                    ->group(function (Router $router) {
                        $router->post('store', 'store')->name('store');
                    });
            });

        return $this;
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }
}
