<?php

namespace Modules\Documents\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Modules\Documents\Entities\DocumentRequest;
use Modules\Documents\Jobs\SendDocumentRequestEmail;

class DocumentRequestService
{
    public function createRequest(array $data): DocumentRequest
    {
        $tenantId = documents_tenant_id();
        $token = Str::random(64);

        $req = DocumentRequest::create([
            'tenant_id' => $tenantId,
            'requested_by' => auth()->id(),
            'document_id' => null,
            'title' => $data['title'] ?? 'Requested document',
            'recipient_email' => $data['recipient_email'] ?? null,
            'recipient_name' => $data['recipient_name'] ?? null,
            'message' => $data['message'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'status' => 'requested',
            'token' => $token,
            'sent_at' => null,
        ]);

        return $req;
    }

    public function sendRequest(DocumentRequest $request): void
    {
        if (!$request->recipient_email) {
            return;
        }
        dispatch(new SendDocumentRequestEmail($request->id));
        $request->forceFill(['sent_at' => now()])->save();
    }
}
