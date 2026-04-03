<?php

namespace Modules\Documents\Observers;

use Modules\Documents\Entities\Document;
use Modules\Documents\Services\Versioning\DocumentSnapshotService;

class DocumentObserver
{
    public function __construct(protected DocumentSnapshotService $snapshots)
    {
    }

    public function updated(Document $document): void
    {
        // Keeping this inactive until explicitly registered.
    }
}
