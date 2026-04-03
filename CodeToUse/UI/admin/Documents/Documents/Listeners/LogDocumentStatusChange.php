<?php

namespace Modules\Documents\Listeners;

use Modules\Documents\Events\DocumentStatusChanged;

class LogDocumentStatusChange
{
    public function handle(DocumentStatusChanged $event): void
    {
        // Intentionally lightweight: history is written by workflow service.
        // This listener is reserved for future audit/export integrations.
    }
}
