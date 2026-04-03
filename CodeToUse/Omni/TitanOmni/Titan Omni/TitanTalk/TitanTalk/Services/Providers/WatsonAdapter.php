<?php
namespace Modules\TitanTalk\Services\Providers;

class WatsonAdapter implements ProviderInterface {
    public function reply(string $message, array $context = []): array {
        // Legacy shim to keep old Watson path callable behind interface
        return ['text' => '[Watson legacy adapter stub]', 'meta' => ['provider' => 'watson']];
    }
}
