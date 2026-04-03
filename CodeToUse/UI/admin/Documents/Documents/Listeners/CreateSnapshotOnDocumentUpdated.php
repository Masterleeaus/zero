<?php

namespace Modules\Documents\Listeners;

use Modules\Documents\Entities\Document;
use Modules\Documents\Services\Versioning\DocumentSnapshotService;

class CreateSnapshotOnDocumentUpdated
{
    public function __construct(protected DocumentSnapshotService $snapshots)
    {
    }

    public function handle(Document $document): void
    {
        // Optional hook if you later register model events.
        $this->snapshots->snapshot($document, auth()->id() ?? null, 'updated');
    }
}
