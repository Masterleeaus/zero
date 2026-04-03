<?php

namespace Modules\Documents\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Entities\Document;

class RebuildDocumentHtml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $documentId) {}

    public function handle(): void
    {
        // Stub: In a later pass, convert markdown -> HTML safely.
        $doc = Document::find($this->documentId);
        if (!$doc) {
            return;
        }
        $doc->body_html = null;
        $doc->save();
    }
}
