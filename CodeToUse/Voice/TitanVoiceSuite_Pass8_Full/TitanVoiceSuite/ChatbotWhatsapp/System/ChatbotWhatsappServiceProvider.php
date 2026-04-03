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
use App\Extensions\ChatbotWhatsapp\System\Voice\Handlers\GenericCommandHandler;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\BusinessHoursService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\CallRouter;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\CallTransferService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\CallbackService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\IvrService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\QueueService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\VoicemailService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\ContextManager;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\IntentRouter;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\PermissionManager;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\ResponseGenerator;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\VoiceCommandParser;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\PersonaResolver;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\AiFallbackService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\OfflineSyncQueueService;
use App\Extensions\ChatbotWhatsapp\System\Voice\Services\UnifiedCommandInterface;

class ChatbotWhatsappServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();

        $this->app->singleton(BusinessHoursService::class);
        $this->app->singleton(IvrService::class);
        $this->app->singleton(QueueService::class);
        $this->app->singleton(CallbackService::class);
        $this->app->singleton(CallTransferService::class);
        $this->app->singleton(VoicemailService::class);
        $this->app->singleton(CallRouter::class);
        $this->app->singleton(VoiceCommandParser::class);
        $this->app->singleton(PermissionManager::class);
        $this->app->singleton(ContextManager::class);
        $this->app->singleton(ResponseGenerator::class);
        $this->app->singleton(PersonaResolver::class);
        $this->app->singleton(AiFallbackService::class);
        $this->app->singleton(OfflineSyncQueueService::class);
        $this->app->singleton(UnifiedCommandInterface::class);
        $this->app->singleton(GenericCommandHandler::class);
        $this->app->singleton(IntentRouter::class, fn ($app) => new IntentRouter($app->make(GenericCommandHandler::class)));
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
        $this->mergeConfigFrom(__DIR__ . '/../config/titan-personas.php', 'titan-personas');

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
                $router->post('{chatbot:uuid}/voice/menu/{conversation}/{channelId}', [VoiceCallController::class, 'menu'])
                    ->name('voice.menu');
                $router->post('{chatbot:uuid}/voice/voicemail/{conversation}/{channelId}', [VoiceCallController::class, 'voicemail'])
                    ->name('voice.voicemail');
                $router->post('{chatbot:uuid}/voice/callback/{conversation}/{channelId}', [VoiceCallController::class, 'callback'])
                    ->name('voice.callback');
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
