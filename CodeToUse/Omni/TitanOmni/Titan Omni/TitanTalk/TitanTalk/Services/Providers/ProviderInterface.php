<?php
namespace Modules\TitanTalk\Services\Providers;

interface ProviderInterface {
    /** Generate a reply given a user message + context */
    public function reply(string $message, array $context = []): array; // ['text'=>..., 'meta'=>...]
}
