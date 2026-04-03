<?php

namespace App\Providers;

use App\Services\Omni\ChannelAdapters\ChatbotAdapter;
use App\Services\Omni\ChannelAdapters\MessengerAdapter;
use App\Services\Omni\ChannelAdapters\TelegramAdapter;
use App\Services\Omni\ChannelAdapters\VoiceAdapter;
use App\Services\Omni\ChannelAdapters\WhatsappAdapter;
use Illuminate\Support\ServiceProvider;

class TitanOmniServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('omni.php'), 'omni');
        $this->mergeConfigFrom(config_path('omni-dual-write.php'), 'omni-dual-write');

        $this->app->singleton(ChatbotAdapter::class);
        $this->app->singleton(WhatsappAdapter::class);
        $this->app->singleton(TelegramAdapter::class);
        $this->app->singleton(MessengerAdapter::class);
        $this->app->singleton(VoiceAdapter::class);
    }

    public function boot(): void
    {
        //
    }
}
