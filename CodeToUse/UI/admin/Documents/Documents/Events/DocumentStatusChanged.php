<?php

namespace Modules\Documents\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Entities\Document;

class DocumentStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Document $document,
        public ?string $fromStatus,
        public ?string $toStatus,
        public ?int $userId = null,
    ) {
    }
}
