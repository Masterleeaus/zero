<?php

namespace Modules\Documents\Listeners;

use Modules\Documents\Events\DocumentStatusChanged;

class EmitDocumentStatusChanged
{
    public function handle(DocumentStatusChanged $event): void
    {
        // Placeholder for integrations (workflows, notifications).
        // Intentionally empty in this pass.
    }
}
