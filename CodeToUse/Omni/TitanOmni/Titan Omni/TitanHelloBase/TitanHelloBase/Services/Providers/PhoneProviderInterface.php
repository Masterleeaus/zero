<?php

namespace Modules\TitanHello\Services\Providers;

interface PhoneProviderInterface
{
    public function providerKey(): string;
    public function validateSignature(array $headers, string $url, array $payload): bool;
    public function mapInbound(array $payload): array;
    public function mapStatus(array $payload): array;
    public function mapRecording(array $payload): array;
}
