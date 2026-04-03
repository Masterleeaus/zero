// === AIConverse P0/P1: provider binding ===
use Modules\TitanTalk\Services\Providers\ProviderInterface;
use Modules\TitanTalk\Services\Providers\AICoreAdapter;
use Modules\TitanTalk\Services\Providers\WatsonAdapter;

$this->app->bind(ProviderInterface::class, function ($app) {
    $driver = config('chatbot.provider', 'aicore');
    return $driver === 'watson' ? new WatsonAdapter() : new AICoreAdapter();
});
