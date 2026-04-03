<?php

namespace Modules\Documents\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Entities\Document;

class GenerateDocumentHtml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $documentId)
    {
    }

    public function handle(): void
    {
        $document = Document::find($this->documentId);
        if (!$document) return;

        // Minimal HTML render stub; can be improved later.
        $document->body_html = '<pre>'.e($document->body_markdown ?? '').'</pre>';
        $document->save();
    }
}
