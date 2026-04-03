<?php

namespace Modules\Documents\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Documents\Entities\DocumentRequest;
use Modules\Documents\Mail\DocumentRequestMail;

class SendDocumentRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $requestId) {}

    public function handle(): void
    {
        $req = DocumentRequest::withoutGlobalScopes()->find($this->requestId);
        if (!$req || !$req->recipient_email) {
            return;
        }

        Mail::to($req->recipient_email)->send(new DocumentRequestMail($req));
    }
}
