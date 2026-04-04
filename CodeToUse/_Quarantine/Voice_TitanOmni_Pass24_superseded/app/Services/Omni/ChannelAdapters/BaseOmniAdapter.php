<?php

namespace App\Services\Omni\ChannelAdapters;

use App\Services\Omni\Contracts\LegacyChannelBridgeContract;
use App\Services\Omni\OmniLegacyConversationMirror;

abstract class BaseOmniAdapter implements LegacyChannelBridgeContract
{
    public function __construct(
        protected OmniLegacyConversationMirror $mirror
    ) {
    }

    public function mirrorToOmni(array $payload): array
    {
        return $this->mirror->mapLegacyPayload($this->driver(), $this->normalize($payload));
    }

    abstract protected function driver(): string;
}
