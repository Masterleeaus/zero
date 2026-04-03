<?php

namespace Modules\FieldItems\Events;

use Illuminate\Queue\SerializesModels;

class MaterialRequested
{
    use SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
