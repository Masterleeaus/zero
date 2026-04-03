<?php

namespace Modules\Documents\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Entities\DocumentRequest;

class DocumentRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DocumentRequest $request) {}

    public function build()
    {
        return $this->subject('Document request: '.$this->request->title)
            ->view('documents::requests.email')
            ->with([
                'request' => $this->request,
                'uploadUrl' => route('documents.request.public', $this->request->token),
            ]);
    }
}
